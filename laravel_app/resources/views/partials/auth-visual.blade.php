{{-- Left panel: brand, illustration, testimonial --}}
<aside class="auth-visual">
    <div class="auth-visual__inner">
        <h1 class="auth-visual__brand">{{ config('app.name', 'FinTrack') }}</h1>
        <div class="auth-visual__art">
            @include('partials.auth-illustration')
        </div>
        @include('partials.auth-testimonial')
    </div>
</aside>
