<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Listing extends Model {
 protected $guarded=[];
 protected function casts(): array { return ['tags'=>'array','source_checked_at'=>'date','published_at'=>'datetime']; }
 public function category(){return $this->belongsTo(Category::class);}
 public function city(){return $this->belongsTo(City::class);}
 public function owner(){return $this->belongsTo(User::class,'owner_id');}
 public function scopeForCity($query, City $city){return $query->where('city_id',$city->id);}
 public function scopePublished($query){return $query->where('status','published');}
 public function getRouteKeyName(){return 'slug';}
 public function getImageAttribute(): string {return $this->photo_path ? '/storage/'.$this->photo_path : '/assets/'.$this->category->slug.'.svg';}
 public function getDirectionsAttribute(): string {return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->name.' '.$this->address.' '.$this->city->name.' Philippines');}
}
