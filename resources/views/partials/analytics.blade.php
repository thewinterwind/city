@php
    $measurementId = (config('analytics.measurement_ids', [])[$city->domain] ?? null);
    $trackPage = !request()->is('admin*', 'dashboard*', 'submit*', 'login', 'register', 'saved');
@endphp
@if($trackPage && is_string($measurementId) && preg_match('/^G-[A-Z0-9]+$/', $measurementId))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @json($measurementId), {
        allow_google_signals: false,
        allow_ad_personalization_signals: false
    });
</script>
@endif
