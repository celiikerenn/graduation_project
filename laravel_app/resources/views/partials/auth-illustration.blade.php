<svg class="auth-illustration" viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="background: transparent;">
    <defs>
        <linearGradient id="authBar" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#60a5fa"/>
            <stop offset="100%" stop-color="#2563eb"/>
        </linearGradient>
    </defs>

    {{-- Bar chart --}}
    <rect x="48" y="175" width="30" height="78" rx="6" fill="url(#authBar)" opacity="0.85"/>
    <rect x="90" y="155" width="30" height="98" rx="6" fill="url(#authBar)"/>
    <rect x="132" y="132" width="30" height="121" rx="6" fill="url(#authBar)" opacity="0.95"/>
    <rect x="174" y="108" width="30" height="145" rx="6" fill="#34d399" opacity="0.9"/>

    {{-- Trend line --}}
    <path d="M64 210 L108 178 L152 182 L196 138" stroke="#6ee7b7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
    <circle cx="196" cy="138" r="5" fill="#6ee7b7"/>

    {{-- Donut / pie chart --}}
    <g transform="translate(288, 178)">
        <path d="M0 0 L0 -50 A50 50 0 0 1 43.3 -25 L26.5 -15.3 A30 30 0 0 0 0 -30 Z" fill="#2563eb"/>
        <path d="M0 0 L43.3 -25 A50 50 0 0 1 15.45 47.55 L9.27 28.53 A30 30 0 0 0 26.5 -15.3 Z" fill="#60a5fa"/>
        <path d="M0 0 L15.45 47.55 A50 50 0 0 1 -40.45 -28.1 L-24.27 -16.86 A30 30 0 0 0 9.27 28.53 Z" fill="#34d399"/>
        <path d="M0 0 L-40.45 -28.1 A50 50 0 0 1 0 -50 L0 -30 A30 30 0 0 0 -24.27 -16.86 Z" fill="#93c5fd" opacity="0.85"/>
        <circle r="28" fill="#1a3358"/>
        <text x="0" y="6" text-anchor="middle" fill="#93c5fd" font-family="Inter,system-ui,sans-serif" font-size="15" font-weight="700">%</text>
    </g>

    {{-- Currency badges --}}
    <g>
        <circle cx="72" cy="68" r="30" fill="rgba(37,99,235,0.3)" stroke="rgba(59,130,246,0.35)" stroke-width="1"/>
        <text x="72" y="76" text-anchor="middle" fill="#93c5fd" font-family="Inter,system-ui,sans-serif" font-size="22" font-weight="700">₺</text>
    </g>
    <g>
        <circle cx="340" cy="58" r="28" fill="rgba(37,99,235,0.25)" stroke="rgba(59,130,246,0.35)" stroke-width="1"/>
        <text x="340" y="66" text-anchor="middle" fill="#bfdbfe" font-family="Inter,system-ui,sans-serif" font-size="20" font-weight="700">$</text>
    </g>
    <g>
        <circle cx="355" cy="248" r="26" fill="rgba(37,99,235,0.22)" stroke="rgba(59,130,246,0.35)" stroke-width="1"/>
        <text x="355" y="255" text-anchor="middle" fill="#6ee7b7" font-family="Inter,system-ui,sans-serif" font-size="18" font-weight="700">€</text>
    </g>

    <line x1="40" y1="255" x2="360" y2="255" stroke="rgba(148,163,184,0.18)" stroke-width="1"/>
</svg>
