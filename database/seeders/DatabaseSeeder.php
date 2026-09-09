<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{City,Category,Listing};
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder {
 public function run():void {DB::transaction(function(){
  $city=City::firstOrCreate(['slug'=>'bacolod'],['name'=>'Bacolod','domain'=>'bacolod.com','tagline'=>'Bacolod, the city of smiles','intro'=>'Great food, little adventures and places to linger. Discover your kind of fun in Bacolod.','active'=>true]);
  $categories=[['hotels-stays','Hotels & Stays','Find your home for a city break.','#e6c6ae'],['resorts-pools','Resorts & Pools','Make room for a little pool time.','#cce5da'],['restaurants','Restaurants','Discover your next delicious meal.','#f5d893'],['cafes-desserts','Cafés & Desserts','Coffee, cake and conversations.','#f1cdbf'],['bars-nightlife','Bars & Nightlife','Find an evening worth going out for.','#b9cfc9'],['activities-tours','Activities & Tours','Try something a little different.','#dae5bb'],['places-to-explore','Places to Explore','Connect your day with local discoveries.','#dce8dc']];
  foreach($categories as $i=>[$slug,$name,$description,$color]){Category::firstOrCreate(['slug'=>$slug],compact('name','description','color')+['position'=>$i+1]);}
  $ids=Category::pluck('id','slug');$data=json_decode(file_get_contents(database_path('data/bacolod.json')),true,512,JSON_THROW_ON_ERROR);
  foreach($data as $row){$row['category_id']=$ids[$row['category']];unset($row['category']);Listing::firstOrCreate(['city_id'=>$city->id,'slug'=>$row['slug']],$row+['status'=>'published','published_at'=>'2026-09-09 12:00:00']);}
 });}
}
