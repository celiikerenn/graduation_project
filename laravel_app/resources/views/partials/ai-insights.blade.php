@php
    $insights = $insights ?? [];
@endphp

@if(count($insights) > 0)
<div class="ai-insights" role="region" aria-label="AI insights">
    @foreach($insights as $insight)
        @php
            $tone = $insight['tone'] ?? 'tip';
            $text = $insight['text'] ?? '';
        @endphp
        @if($text !== '')
            <article class="ai-insight ai-insight--{{ $tone }}">
                <span class="ai-insight__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="8" width="14" height="11" rx="2"></rect>
                        <path d="M9 8V6a3 3 0 0 1 6 0v2"></path>
                        <circle cx="9.5" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="14.5" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17h4"></path>
                    </svg>
                </span>
                <div class="ai-insight__body">
                    <div class="ai-insight__label">AI insight</div>
                    <p class="ai-insight__text">{{ $text }}</p>
                </div>
            </article>
        @endif
    @endforeach
</div>
@endif
