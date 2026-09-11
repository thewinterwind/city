<?php
namespace Tests\Feature;

use App\Models\{Category, City, Listing};
use Database\Seeders\BacolodExpansionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpansionTest extends TestCase
{
    use RefreshDatabase;

    public function test_expansion_is_complete_repeatable_and_preserves_live_editorial_changes(): void
    {
        $this->seed();
        $existing = Listing::where('slug', 'l-fisher-hotel')->firstOrFail();
        $existing->update(['editorial_rank' => 1, 'description' => 'An owner-reviewed description.', 'photo_path' => 'owner-photo.jpg']);
        $before = $existing->only(['name', 'description', 'editorial_rank', 'editorial_photo', 'photo_path']);
        $this->seed(BacolodExpansionSeeder::class);
        $this->assertSame($before, $existing->fresh()->only(array_keys($before)));
        $this->assertNotEmpty($existing->fresh()->details['sections']);
        $this->assertGreaterThanOrEqual(4, count($existing->fresh()->gallery));
        $count = Listing::count();
        $this->assertGreaterThanOrEqual(100, $count);
        foreach (Category::all() as $category) {
            $this->assertGreaterThan(5, Listing::where('category_id', $category->id)->count());
        }
        $new = Listing::where('slug', 'citadines-bacolod-city')->firstOrFail();
        $new->update(['description' => 'Later approved edit.', 'editorial_rank' => 2, 'details' => ['highlights' => ['Reviewed facilities']]]);
        $this->seed(BacolodExpansionSeeder::class);
        $this->assertSame($count, Listing::count());
        $this->assertSame('Later approved edit.', $new->fresh()->description);
        $this->assertSame(2, $new->fresh()->editorial_rank);
        $this->assertSame(['Reviewed facilities'], $new->fresh()->details['highlights']);
        foreach (Listing::all() as $place) {
            $this->assertNotEmpty($place->source_url);
            $this->assertNotEmpty($place->details['highlights']);
            foreach ($place->gallery ?? [] as $photo) $this->assertFileExists(public_path('assets/places/'.$photo['path']));
            if ($place->editorial_photo) $this->assertFileExists(public_path('assets/places/'.$place->editorial_photo));
        }
    }

    public function test_enriched_pages_gallery_and_pagination_render_without_cross_city_leaks(): void
    {
        $this->seed();
        $this->seed(BacolodExpansionSeeder::class);
        $this->get('/places/l-fisher-hotel')->assertOk()->assertSee('Rooms and accommodation')->assertSee('data-place-gallery', false)->assertSee('data-photo-next', false)->assertSee('Photo credits');
        $this->get('/places/citadines-bacolod-city')->assertOk()->assertSee('Check-in');
        $this->get('/explore?category=hotels-stays&page=2')->assertOk()->assertSee('Page 2 of 2')->assertSee('Circle Inn');
        $this->get('/places/bernardino-jalandoni-museum')->assertOk()->assertSee('https://creativecommons.org/licenses/by-sa/4.0/', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/places/citadines-bacolod-city');
        $other = City::create(['name'=>'Other', 'slug'=>'gallery-other', 'domain'=>'other.test', 'tagline'=>'Other', 'intro'=>'Other', 'active'=>true]);
        $this->get('http://other.test/places/citadines-bacolod-city')->assertNotFound();
        $place = Listing::where('slug', 'citadines-bacolod-city')->firstOrFail();
        $place->update(['gallery'=>[['path'=>'../../untrusted.svg','caption'=>'Unsafe']], 'details'=>['sections'=>[['title'=>'<script>alert(1)</script>', 'body'=>'Escaped content']]]]);
        $this->get('http://bacolod.com/places/citadines-bacolod-city')->assertOk()->assertDontSee('../../untrusted.svg', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
