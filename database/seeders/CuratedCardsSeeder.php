<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{City,Listing};
class CuratedCardsSeeder extends Seeder {
 public function run():void {
  DB::transaction(function(){
   $city=City::where('slug','bacolod')->firstOrFail();
   $data=json_decode(file_get_contents(database_path('data/bacolod.json')),true,512,JSON_THROW_ON_ERROR);
   foreach($data as $row){
    $listing=Listing::forCity($city)->where('slug',$row['slug'])->first();
    if(!$listing || $listing->editorial_photo!==null)continue;
    $fields=[];
    // Backfill the new card metadata only; preserve live values and owner uploads.
    foreach(['editorial_rank','summary','editorial_photo','editorial_photo_credit','editorial_photo_source_url','editorial_photo_license'] as $key){
     if($listing->$key===null && isset($row[$key]))$fields[$key]=$row[$key];
    }
    if($fields)$listing->update($fields);
   }
  });
 }
}
