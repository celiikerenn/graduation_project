@php
    $name = $name ?? 'info-circle';
@endphp
<svg class="insight-card__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('bulb')
        <path d="M9 18h6"></path>
        <path d="M10 22h4"></path>
        <path d="M12 2a7 7 0 0 1 7 7c0 2.38 -1.19 4.47 -3 5.74v2.26a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1v-2.26c-1.81 -1.27 -3 -3.36 -3 -5.74a7 7 0 0 1 7 -7z"></path>
        @break
    @case('pin')
        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path>
        @break
    @case('circle-check')
        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
        <path d="M9 12l2 2l4 -4"></path>
        @break
    @case('rocket')
        <path d="M4.5 16.5c-1.06 0 -1.958 .714 -2.204 1.684a9 9 0 0 0 12.408 12.408a9 9 0 0 0 1.684 -2.204c.77 -2.148 -.38 -4.584 -2.592 -4.848l-1.856 -.232a1 1 0 0 1 -.783 -.649l-.57 -2.28a1 1 0 0 0 -.98 -.804h-1.328a1 1 0 0 0 -.98 .804l-.57 2.28a1 1 0 0 1 -.783 .649l-1.856 .232c-2.212 .264 -3.362 2.7 -2.592 4.848"></path>
        <path d="M10 15l2 -2"></path>
        @break
    @default
        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
        <path d="M12 8v4"></path>
        <path d="M12 16h.01"></path>
@endswitch
</svg>
