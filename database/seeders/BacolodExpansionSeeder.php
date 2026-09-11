<?php
namespace Database\Seeders;

use App\Models\{Category, City, Listing};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Explicit editorial batch: append places and fill empty enrichment fields only. */
class BacolodExpansionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $city = City::where('slug', 'bacolod')->firstOrFail();
            $categories = Category::pluck('id', 'slug');
            $rows = json_decode(file_get_contents(database_path('data/bacolod-expansion.json')), true, 512, JSON_THROW_ON_ERROR);
            foreach ($rows as $row) {
                $category = $row['category'];
                unset($row['category']);
                $row['category_id'] = $categories[$category];
                // Existing rankings, owner edits and uploaded photos are authoritative.
                if (!Listing::forCity($city)->where('slug', $row['slug'])->exists()) {
                    $rank = Listing::forCity($city)->where('category_id', $row['category_id'])->max('editorial_rank');
                    Listing::create($row + ['city_id' => $city->id, 'status' => 'published', 'published_at' => now(), 'editorial_rank' => max(5, (int) $rank) + 1]);
                }
            }
            $details = json_decode(file_get_contents(database_path('data/bacolod-details.json')), true, 512, JSON_THROW_ON_ERROR);
            foreach ($details as $slug => $enrichment) {
                $listing = Listing::forCity($city)->where('slug', $slug)->first();
                if (!$listing || $listing->status !== 'published') continue;
                $changes = [];
                foreach (['details', 'gallery'] as $field) {
                    if (empty($listing->$field) && !empty($enrichment[$field])) $changes[$field] = $enrichment[$field];
                }
                if (isset($changes['details'])) $changes['source_checked_at'] = $enrichment['source_checked_at'];
                if ($changes) $listing->update($changes);
            }
        });
    }
}
