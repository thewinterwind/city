<?php
namespace App\Http\Controllers;
use App\Models\{Category,City,Listing};
use Illuminate\Http\Request;
class DirectoryController {
 public function home(City $city) {
  $categories=Category::orderBy('position')->get();
  $counts=Listing::forCity($city)->published()->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total','category_id');
  $picks=Listing::forCity($city)->published()->with(['category','city'])->whereNotNull('featured_position')->orderBy('featured_position')->limit(4)->get();
  return view('home',compact('categories','counts','picks'));
 }
 public function explore(Request $request,City $city) {
  $filters=$request->validate(['q'=>'nullable|string|max:120','category'=>'nullable|string|max:80','occasion'=>'nullable|in:family,date-night,friends,rainy-day,weekend','area'=>'nullable|string|max:80','sort'=>'nullable|in:name,newest','saved'=>'nullable|string|max:1000']);
  $categories=Category::orderBy('position')->get();
  $query=Listing::forCity($city)->published()->with(['category','city']);
  if($request->filled('category')){$cat=$categories->firstWhere('slug',$filters['category']);abort_unless($cat,404);$query->where('category_id',$cat->id);}
  if($request->filled('q')){$q='%'.str_replace(['\\','%','_'],['\\\\','\\%','\\_'],$filters['q']).'%';$query->where(fn($b)=>$b->where('name','like',$q)->orWhere('description','like',$q)->orWhere('area','like',$q));}
  if($request->filled('occasion')){$query->whereJsonContains('tags',$filters['occasion']);}
  if($request->filled('area')){$query->where('area',$filters['area']);}
  if($request->has('saved')){$ids=array_slice(array_filter(explode(',',$filters['saved'] ?? ''),fn($id)=>ctype_digit($id)),0,50);$query->whereIn('id',$ids);}
  $listings=($request->input('sort')==='newest' ? $query->orderByDesc('published_at')->orderBy('name') : $query->orderBy('name'))->paginate(12)->withQueryString();
  $areas=Listing::forCity($city)->published()->distinct()->orderBy('area')->pluck('area');
  return view('explore',compact('categories','listings','areas','filters'));
 }
 public function show(City $city,string $slug) {
  $listing=Listing::forCity($city)->published()->with(['category','city'])->where('slug',$slug)->firstOrFail();
  $related=Listing::forCity($city)->published()->with(['category','city'])->where('category_id',$listing->category_id)->whereKeyNot($listing->id)->orderBy('name')->limit(3)->get();
  return view('listing',compact('listing','related'));
 }
 public function sitemap(City $city){$listings=Listing::forCity($city)->published()->get(['slug','updated_at']);$categories=Category::all();return response()->view('sitemap',compact('listings','categories'))->header('Content-Type','application/xml');}
}
