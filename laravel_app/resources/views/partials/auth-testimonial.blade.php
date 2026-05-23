@php
    $testimonials = [
        [
            'initials' => 'AY',
            'name' => 'Aylin Y.',
            'role' => 'FinTrack user',
            'quote' => 'FinTrack helped me take control of my spending—saving money has never been easier!',
        ],
        [
            'initials' => 'MK',
            'name' => 'Mehmet K.',
            'role' => 'Freelancer',
            'quote' => 'I finally see where my money goes each month. The charts make patterns obvious at a glance.',
        ],
        [
            'initials' => 'ZA',
            'name' => 'Zeynep A.',
            'role' => 'FinTrack user',
            'quote' => 'Receipt scan fills in amount and category in seconds. I barely type anything anymore.',
        ],
        [
            'initials' => 'CD',
            'name' => 'Can D.',
            'role' => 'Small business owner',
            'quote' => 'Monthly PDF and CSV exports are ready in one click. Bookkeeping got much simpler.',
        ],
        [
            'initials' => 'ES',
            'name' => 'Elif S.',
            'role' => 'FinTrack user',
            'quote' => 'Once I set a monthly budget, the dashboard warnings stopped me from overspending twice.',
        ],
        [
            'initials' => 'BT',
            'name' => 'Burak T.',
            'role' => 'Student',
            'quote' => 'Simple layout, clear categories, and I can track fixed bills alongside daily expenses.',
        ],
    ];
@endphp

<div class="auth-testimonial-carousel" data-interval="4200" aria-live="polite" aria-atomic="true">
    <div class="auth-testimonial-track">
        <div class="auth-testimonial-slider">
            @foreach($testimonials as $index => $item)
                <blockquote
                    class="auth-testimonial auth-testimonial-slide{{ $index === 0 ? ' is-active' : '' }}"
                    aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                >
                    <p class="auth-testimonial__quote">&ldquo;{{ $item['quote'] }}&rdquo;</p>
                    <footer class="auth-testimonial__author">
                        <span class="auth-testimonial__avatar" aria-hidden="true">{{ $item['initials'] }}</span>
                        <span>
                            <cite class="auth-testimonial__name">{{ $item['name'] }}</cite>
                            <span class="auth-testimonial__role">{{ $item['role'] }}</span>
                        </span>
                    </footer>
                </blockquote>
            @endforeach
        </div>
    </div>
    <div class="auth-testimonial-dots" aria-hidden="true">
        @foreach($testimonials as $index => $item)
            <span class="auth-testimonial-dot{{ $index === 0 ? ' is-active' : '' }}"></span>
        @endforeach
    </div>
</div>
