<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{City,Category,Listing,Submission,User};
use App\Services\ReviewSubmission;
class DirectoryTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed();}
 private function user(string $name='owner'):User{return User::create(['name'=>$name,'email'=>$name.'@example.com','password'=>'a-long-test-password']);}
 public function test_seed_is_idempotent_and_all_categories_have_at_most_ten_places():void{$this->seed();$this->assertSame(count(json_decode(file_get_contents(database_path('data/bacolod.json')),true)),Listing::count());foreach(Category::all() as $c){$n=Listing::where('category_id',$c->id)->count();$this->assertGreaterThanOrEqual(4,$n);$this->assertLessThanOrEqual(10,$n);}}
 public function test_public_pages_and_filters_render():void{foreach(['/','/explore','/plan','/saved','/about','/privacy','/login','/register','/sitemap.xml','/robots.txt','/places/aboys-restaurant'] as $path){$this->get($path)->assertOk();}$this->get('/explore?category=restaurants&q=Chicken')->assertSee('Chicken House')->assertDontSee('Marapara Pool Bar');$this->get('/explore?occasion=rainy-day')->assertDontSee('Marapara Pool Bar');$this->get('/explore?category=nonexistent')->assertNotFound();}
 public function test_city_isolation_and_unknown_hosts():void{$other=City::create(['name'=>'Other','slug'=>'other','domain'=>'other.test','tagline'=>'Other','intro'=>'Other','active'=>true]);$p=Listing::first()->replicate();$p->city_id=$other->id;$p->name='Private to another city';$p->slug='other-place';$p->save();$this->get('/explore')->assertDontSee('Private to another city');$this->get('/places/other-place')->assertNotFound();$this->get('http://unknown.test/')->assertNotFound();}
 public function test_unauthorised_visitors_cannot_manage_or_edit_listings():void{$this->get('/dashboard')->assertRedirect('/login');$this->actingAs($this->user())->get('/admin')->assertForbidden();$this->get('/submit/aboys-restaurant?type=edit')->assertForbidden();}
 public function test_claims_are_pending_and_public_places_cannot_be_claimed():void{$u=$this->user();$p=Listing::where('slug','aboys-restaurant')->first();$this->actingAs($u)->post('/submit',['type'=>'claim','listing_id'=>$p->id,'relationship'=>'Owner','contact_email'=>'owner@example.com','evidence'=>'An official website identifies me as the authorised business owner.'])->assertRedirect('/dashboard');$this->assertNull($p->fresh()->owner_id);$this->assertSame('pending',Submission::first()->status);$public=Listing::where('kind','public_place')->first();$this->post('/submit',['type'=>'claim','listing_id'=>$public->id])->assertForbidden();}
 public function test_approval_updates_only_requested_fields_and_keeps_photo():void{$u=$this->user();$admin=$this->user('admin');$admin->forceFill(['is_admin'=>true])->save();$p=Listing::where('slug','aboys-restaurant')->first();$p->update(['owner_id'=>$u->id,'photo_path'=>'existing.jpg']);$s=Submission::create(['city_id'=>$p->city_id,'listing_id'=>$p->id,'user_id'=>$u->id,'type'=>'edit','payload'=>['name'=>'Updated business name']]);app(ReviewSubmission::class)->handle($s,$admin,true,'Verified');$this->assertSame('Updated business name',$p->fresh()->name);$this->assertSame('existing.jpg',$p->fresh()->photo_path);$this->assertDatabaseCount('audit_events',1);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);app(ReviewSubmission::class)->handle($s,$admin,true,'Again');}
 public function test_submissions_reject_script_urls_and_cross_city_ids():void{$u=$this->user();$this->actingAs($u)->post('/submit',['type'=>'new','name'=>'Bad link','description'=>str_repeat('description ',5),'address'=>'Street','area'=>'Downtown','category_id'=>Category::first()->id,'website'=>'javascript:alert(1)'])->assertSessionHasErrors('website');$other=City::create(['name'=>'Other','slug'=>'other','domain'=>'other.test','tagline'=>'Other','intro'=>'Other','active'=>true]);$p=Listing::first()->replicate();$p->city_id=$other->id;$p->save();$this->post('/submit',['type'=>'correction','listing_id'=>$p->id,'details'=>'Please correct the contact details.'])->assertSessionHasErrors('listing_id');}
 public function test_registration_cannot_grant_admin_and_password_changes_need_current_password():void{$this->post('/register',['name'=>'New Owner','email'=>'new@example.com','password'=>'a-long-password-123','password_confirmation'=>'a-long-password-123','accept'=>'1','is_admin'=>true])->assertRedirect('/dashboard');$this->assertFalse(User::where('email','new@example.com')->first()->is_admin);$this->post('/password',['current_password'=>'wrong','password'=>'replacement-password','password_confirmation'=>'replacement-password'])->assertSessionHasErrors('current_password');}
 public function test_saved_links_cannot_expose_archived_or_other_city_listings():void{$p=Listing::first();$p->update(['status'=>'archived']);$this->get('/explore?saved='.$p->id)->assertOk()->assertDontSee($p->name);$this->get('/places/'.$p->slug)->assertNotFound();}

 public function test_home_has_ranked_category_rows_and_preserves_city_and_visibility_scope():void {
  $a=Listing::where('slug','aboys-restaurant')->first();$b=Listing::where('slug','chicken-house-lacson')->first();
  $a->update(['editorial_rank'=>2]);$b->update(['editorial_rank'=>1]);
  Listing::where('slug','ripples')->update(['status'=>'archived']);
  $other=City::create(['name'=>'Other','slug'=>'other-home','domain'=>'other-home.test','tagline'=>'Other','intro'=>'Other','active'=>true]);
  $private=$a->replicate();$private->city_id=$other->id;$private->name='Another city hidden place';$private->save();
  $response=$this->get('/')->assertOk()->assertSee('data-carousel',false)->assertSee('See all restaurants')->assertSee('track-restaurants')->assertDontSee('Another city hidden place')->assertDontSee('Ripples at')->assertDontSee('hero-search',false);
  $response->assertSeeInOrder(['Chicken House','Aboy']);
  $this->get('/explore?category=restaurants')->assertSeeInOrder(['Chicken House','Aboy']);
 }
 public function test_admin_rank_changes_are_scoped_and_survive_card_backfill():void {
  $admin=$this->user('rank-admin');$admin->update(['is_admin'=>true]);
  $place=Listing::where('slug','aboys-restaurant')->first();$city=$place->city;
  $otherCategory=Listing::where('slug','park-inn-bacolod')->first();$before=$otherCategory->editorial_rank;
  $this->actingAs($admin)->post('/admin/listings/'.$place->id.'/rank',['rank'=>2])->assertRedirect();
  $this->assertSame(2,$place->fresh()->editorial_rank);$this->assertSame($before,$otherCategory->fresh()->editorial_rank);
  $this->post('/admin/listings/'.$place->id.'/rank',['rank'=>''])->assertRedirect();
  $this->seed(\Database\Seeders\CuratedCardsSeeder::class);$this->assertNull($place->fresh()->editorial_rank);
  $this->actingAs($this->user('rank-owner'))->post('/admin/listings/'.$place->id.'/rank',['rank'=>1])->assertForbidden();
  $other=City::create(['name'=>'Other','slug'=>'rank-other','domain'=>'rank-other.test','tagline'=>'Other','intro'=>'Other','active'=>true]);
  $private=$place->replicate();$private->city_id=$other->id;$private->save();
  $this->actingAs($admin)->post('/admin/listings/'.$private->id.'/rank',['rank'=>1])->assertNotFound();
 }
}
