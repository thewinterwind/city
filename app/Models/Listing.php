<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Listing extends Model {
 protected $guarded=[];
 protected function casts(): array { return ['tags'=>'array','details'=>'array','gallery'=>'array','source_checked_at'=>'date','published_at'=>'datetime']; }
 public function category(){return $this->belongsTo(Category::class);}
 public function city(){return $this->belongsTo(City::class);}
 public function owner(){return $this->belongsTo(User::class,'owner_id');}
 public function scopeForCity($query, City $city){return $query->where('city_id',$city->id);}
 public function scopePublished($query){return $query->where('status','published');}
 public function scopeRanked($query){return $query->orderByRaw('CASE WHEN editorial_rank IS NULL THEN 1 ELSE 0 END')->orderBy('editorial_rank')->orderBy('name')->orderBy('id');}
 public function getRouteKeyName(){return 'slug';}
 public function getImageAttribute(): string {return $this->photo_path ? '/storage/'.$this->photo_path : ($this->editorial_photo ? '/assets/places/'.$this->editorial_photo : '/assets/'.$this->category->slug.'.svg');}
 public function getHasPhotoAttribute(): bool {return (bool)($this->photo_path || $this->editorial_photo);}
 public function getCardSummaryAttribute(): string {return $this->summary ?: preg_split('/(?<=[.!?])\s+/u',trim($this->description),2)[0];}
 public function getPhotosAttribute(): array {
  $photos=[];
  if($this->has_photo){$photos[]=['url'=>$this->image,'caption'=>$this->name,'credit'=>$this->photo_path ? $this->photo_credit : $this->editorial_photo_credit,'source_url'=>$this->photo_path ? null : $this->editorial_photo_source_url,'license'=>$this->photo_path ? null : $this->editorial_photo_license];}
  foreach($this->gallery ?? [] as $photo){
   if(!empty($photo['path']) && preg_match('~^[a-z0-9/_-]+\\.(jpg|jpeg|png|webp)$~i',$photo['path'])){$photo['url']='/assets/places/'.$photo['path'];$index=array_search($photo['url'],array_column($photos,'url'),true);if($index===false)$photos[]=$photo;else $photos[$index]=array_merge($photos[$index],$photo);}
  }
  foreach($photos as &$photo){if(!empty($photo['credit']))$photo['credit']=implode(' / ',array_unique(explode(' / ',$photo['credit'])));}
  unset($photo);
  return $photos;
 }
 public function getDirectionsAttribute(): string {return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->name.' '.$this->address.' Philippines');}
}
