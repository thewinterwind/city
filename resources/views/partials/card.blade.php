<article class="place-card">
 <div class="card-image"><a href="/places/{{ $listing->slug }}" tabindex="-1" aria-hidden="true"><img loading="{{ ($eagerPhoto??false)?'eager':'lazy' }}" src="{{ $listing->image }}" alt="{{ $listing->has_photo?$listing->name:'' }}" width="720" height="480" decoding="async"></a>
 @unless($listing->has_photo)<span class="illustration-label">Photo coming soon</span>@endunless
 @if(($homeCard??false) && $listing->editorial_rank)<span class="rank-badge" aria-label="Editorial pick {{ $listing->editorial_rank }}">#{{ $listing->editorial_rank }}</span>@endif
 <button type="button" class="save-button" data-save="{{ $listing->id }}" aria-label="Save {{ $listing->name }}" aria-pressed="false">♡</button></div>
 <div class="card-body">
 @unless($homeCard??false)<span class="category-label">{{ $listing->category->name }}</span>@endunless
 <p class="card-area"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{ $listing->area }}</p>
 <h3><a href="/places/{{ $listing->slug }}">{{ $listing->name }}</a></h3>
 <p class="card-description">{{ $listing->card_summary }}</p>
 </div>
</article>
