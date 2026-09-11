@extends('layout')
@section('title',$listing->name)
@section('description',Str::limit($listing->description,155))
@section('content')
@php($photos=$listing->photos)
@php($details=$listing->details ?? [])
<div class="container place-detail">
 <div class="breadcrumbs"><a href="/explore">Explore</a> / <a href="/explore?category={{ $listing->category->slug }}">{{ $listing->category->name }}</a> / {{ $listing->name }}</div>
 <div class="detail-heading"><div><span class="eyebrow">{{ $listing->category->name }} · {{ $listing->area }}</span><h1>{{ $listing->name }}</h1><p class="place-summary">{{ $listing->card_summary }}</p></div><button class="button outline" data-save="{{ $listing->id }}" aria-pressed="false">Save this place ♡</button></div>
 @if(count($photos))
 <section class="place-gallery" aria-label="Photos of {{ $listing->name }}" data-place-gallery>
  <div class="photo-mosaic mosaic-{{ min(count($photos),5) }}">
  @foreach(array_slice($photos,0,5) as $i=>$photo)<a class="gallery-tile" href="{{ $photo['url'] }}" data-open-photo="{{ $i }}" aria-label="View photo {{ $i+1 }}: {{ $photo['caption'] }}"><img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] }}" @if($i>0) loading="lazy" @endif width="1200" height="800">@if($i===min(count($photos),5)-1 && count($photos)>1)<span class="gallery-all">View all {{ count($photos) }} photos</span>@endif</a>@endforeach
  </div>
  <dialog class="photo-dialog" aria-label="Photo gallery for {{ $listing->name }}">
   <div class="photo-dialog-header"><span>{{ $listing->name }}</span><button type="button" data-close-gallery aria-label="Close gallery">Close ×</button></div>
   <div class="photo-stage"><button type="button" data-photo-prev aria-label="Previous photo">‹</button><img data-gallery-image src="{{ $photos[0]['url'] }}" alt="{{ $photos[0]['caption'] }}"><button type="button" data-photo-next aria-label="Next photo">›</button></div>
   <div class="photo-caption" aria-live="polite"><span data-photo-count></span><strong data-photo-caption></strong><span data-photo-credit></span></div>
   <div class="photo-thumbnails" aria-label="Choose a photo">@foreach($photos as $i=>$photo)<button type="button" data-photo-index="{{ $i }}" aria-label="Photo {{ $i+1 }}: {{ $photo['caption'] }}"><img src="{{ $photo['url'] }}" alt="" loading="lazy"></button>@endforeach</div>
  </dialog>
  <script type="application/json" data-gallery-json>{!! json_encode($photos,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) !!}</script>
 </section>
 @else<div class="detail-image"><img src="{{ $listing->image }}" alt="Category illustration for {{ $listing->category->name }}"><span class="illustration-label">Venue photos welcome</span></div>@endif
 <div class="detail-grid place-content">
 <div>
  <section class="detail-description"><h2>About {{ $listing->name }}</h2>@foreach(preg_split('/\n\s*\n/',trim($listing->description)) as $paragraph)<p>{{ $paragraph }}</p>@endforeach
  @if(!empty($details['highlights']))<ul class="place-highlights">@foreach($details['highlights'] as $highlight)<li>{{ $highlight }}</li>@endforeach</ul>@endif
  <div class="tags">@foreach($listing->tags as $tag)<a href="/explore?occasion={{ $tag }}">{{ ['family'=>'Family time','date-night'=>'Date night','friends'=>'With friends','rainy-day'=>'Rainy-day ideas','weekend'=>'Weekend plans'][$tag]??$tag }}</a>@endforeach</div>
  </section>
  @foreach($details['sections'] ?? [] as $section)<section class="place-section"><h2>{{ $section['title'] }}</h2>@if(!empty($section['body']))<p>{{ $section['body'] }}</p>@endif @if(!empty($section['items']))<ul>@foreach($section['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>@endif</section>@endforeach
  @if(count($photos))<details class="photo-attribution"><summary>Photo credits</summary><ul>@foreach($photos as $photo)<li>{{ $photo['caption'] }} — @if(!empty($photo['source_url']) && preg_match('~^https?://~',$photo['source_url']))<a href="{{ $photo['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ $photo['credit'] ?? 'Source' }}</a>@else{{ $photo['credit'] ?? 'Contributor' }}@endif @if(!empty($photo['license'])) · @if(!empty($photo['license_url']) && str_starts_with($photo['license_url'],'https://creativecommons.org/'))<a href="{{ $photo['license_url'] }}" target="_blank" rel="noopener noreferrer">{{ $photo['license'] }}</a>@else{{ $photo['license'] }}@endif @endif @if(!empty($photo['changes'])) · {{ $photo['changes'] }}@endif</li>@endforeach</ul></details>@endif
 </div>
 <aside><div class="visit-card"><h2>Plan your visit</h2><h3>Location</h3><p>{{ $listing->address }}</p><a class="button" href="{{ $listing->directions }}" target="_blank" rel="noopener noreferrer">Get directions ↗</a>@if($listing->website)<a class="button outline" href="{{ $listing->website }}" target="_blank" rel="noopener noreferrer">Visit {{ str_contains($listing->website,'facebook.com')?'business page':'website' }} ↗</a>@endif @if($listing->phone)<a class="button outline" href="tel:{{ preg_replace('/[^+0-9]/','',$listing->phone) }}">Call {{ $listing->phone }}</a>@endif
 @if(!empty($details['facts']))<dl class="place-facts">@foreach($details['facts'] as $label=>$value)<div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>@endforeach</dl>@endif
 <p class="small muted">Confirm current hours, prices and availability directly with the venue.</p></div>
 <div class="source-card"><strong>Information sources</strong>@if($listing->source_url)<p>Checked {{ $listing->source_checked_at?->format('M j, Y') }}.</p><a href="{{ $listing->source_url }}" target="_blank" rel="noopener noreferrer">Venue information ↗</a>@endif
 @foreach($details['sources'] ?? [] as $source)@if($source['url']!==$listing->source_url)<p><a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $source['label'] }} ↗</a></p>@endif @endforeach
 <p><a href="/submit/{{ $listing->slug }}?type=correction">Suggest a correction</a></p>@if($listing->kind==='business' && !$listing->owner_id)<p>Is this your business?<br><a href="/submit/{{ $listing->slug }}?type=claim">Claim this listing for free ↗</a></p>@elseif($listing->kind==='public_place')<p class="small">Public place · Not available for business claims.</p>@endif</div>
 </aside></div>
 @if($related->count())<section class="section"><div class="section-heading"><h2>More {{ strtolower($listing->category->name) }}</h2><a href="/explore?category={{ $listing->category->slug }}">See all ↗</a></div><div class="cards">@foreach($related as $listing)@include('partials.card')@endforeach</div></section>@endif
</div>
@endsection
