<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($slug)
@case('hotels-stays')<path d="M3 18V6m18 12V9M3 14h18M3 9h18M7 9V6h5v3m2 0V6h5v3M3 18v2m18-2v2"/>@break
@case('resorts-pools')<path d="M3 16c3-3 3 3 6 0s3 3 6 0 3 3 6 0M3 21c3-3 3 3 6 0s3 3 6 0 3 3 6 0M7 13V5a2 2 0 0 1 4 0m3 10V5a2 2 0 0 1 4 0M7 8h7m-7 4h7"/>@break
@case('restaurants')<path d="M5 3v6m3-6v6M2 3v6a3 3 0 0 0 6 0m-3 3v9M20 3c-5 2-6 8-2 9h2m0-9v18"/>@break
@case('cafes-desserts')<path d="M3 8h13v6a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8Zm13 1h2a3 3 0 0 1 0 6h-2M2 22h17M6 2v2m4-2v2m4-2v2"/>@break
@case('bars-nightlife')<path d="m3 4 9 10 9-10H3Zm9 10v7m-4 0h8M6 8h12"/>@break
@case('activities-tours')<circle cx="12" cy="12" r="10"/><path d="m16 8-2 6-6 2 2-6 6-2Z"/>@break
@default<path d="m3 21 4-18 5 3 5-3 4 18-7-3-5 3-6-3m4-15 2 18m3-15 2 12m3-15-3 15"/>
@endswitch
</svg>
