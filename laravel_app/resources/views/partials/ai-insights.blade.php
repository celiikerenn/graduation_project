@php
    $insights = $insights ?? [];
@endphp

@if(count($insights) > 0)
<div class="insight-cards" role="region" aria-label="Tips">
    @foreach($insights as $insight)
        @php
            $title = $insight['title'] ?? 'Tip';
            $text = $insight['text'] ?? '';
            $icon = $insight['icon'] ?? 'info-circle';
            $variant = $insight['variant'] ?? 'blue';
        @endphp
        @if($text !== '')
            <article class="insight-card insight-card--{{ $variant }}">
                <span class="insight-card__icon" aria-hidden="true">
                    @include('partials.insight-icon', ['name' => $icon])
                </span>
                <div class="insight-card__body">
                    <p class="insight-card__title">{{ $title }}</p>
                    <p class="insight-card__text">{{ $text }}</p>
                </div>
            </article>
        @endif
    @endforeach
</div>
@endif
