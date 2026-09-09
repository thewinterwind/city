@extends('layout')
@section('title','Places to eat, stay & explore in '.$city->name)
@section('body-class','directory-home')
@section('content')
<div class="container home-listings">
 <h1 class="sr-only">Places to eat, stay and explore in {{ $city->name }}</h1>
 @foreach($rows as $row)
 @php($category=$row['category'])
 <section class="category-row theme-{{ $category->slug }}" id="{{ $category->slug }}" aria-labelledby="heading-{{ $category->slug }}" data-carousel>
  <div class="row-heading">
   <div class="row-title"><span class="category-icon" aria-hidden="true">@include('partials.category-icon',['slug'=>$category->slug])</span><h2 id="heading-{{ $category->slug }}">{{ $category->name }}</h2><span class="row-count">{{ $row['count'] }} places</span></div>
   <div class="row-controls"><button type="button" class="carousel-arrow" data-direction="-1" aria-label="Previous {{ strtolower($category->name) }}" aria-controls="track-{{ $category->slug }}" disabled><span aria-hidden="true">←</span></button><button type="button" class="carousel-arrow" data-direction="1" aria-label="More {{ strtolower($category->name) }}" aria-controls="track-{{ $category->slug }}" disabled><span aria-hidden="true">→</span></button></div>
  </div>
  <div class="card-track" id="track-{{ $category->slug }}" tabindex="0" role="region" aria-label="{{ $category->name }} places, scroll to browse">
   @forelse($row['listings'] as $listing)
    @include('partials.card',['homeCard'=>true,'eagerPhoto'=>$loop->parent->first && $loop->index<4])
   @empty
    <p class="row-empty">Places are being added to this category.</p>
   @endforelse
  </div>
  <a class="see-category" href="/explore?category={{ $category->slug }}">See all {{ strtolower($category->name) }} <span aria-hidden="true">↗</span></a>
 </section>
 @endforeach
</div>
@endsection
