<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #0a0f18;
            --navy-left: #1a3358;
            --navy-left-mid: #1f3d66;
            --navy-panel: #0d2137;
            --navy-mid: #0a1628;
            --blue: #2563eb;
            --blue-glow: rgba(37, 99, 235, 0.5);
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(255, 255, 255, 0.12);
            --red: #f87171;
            --green: #4ade80;
        }

        * { box-sizing: border-box; }

        body.auth-page {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--navy-panel);
            -webkit-font-smoothing: antialiased;
        }

        .auth-split {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .auth-split__visual {
            flex: 1;
            display: none;
            background: var(--navy-left);
        }
        @media (min-width: 960px) {
            .auth-split__visual { display: flex; }
        }

        .auth-visual {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            padding: 48px 36px;
            background: linear-gradient(165deg, var(--navy-left-mid) 0%, var(--navy-left) 55%, #173556 100%);
        }
        .auth-visual__inner {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 28px;
        }
        .auth-visual__brand {
            margin: 0;
            padding: 0;
            border: none;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        .auth-visual__art {
            width: 100%;
            display: flex;
            justify-content: center;
            background: transparent;
            border: none;
            box-shadow: none;
        }
        .auth-illustration {
            display: block;
            width: 100%;
            max-width: 380px;
            height: auto;
            background: transparent;
            border: none;
            box-shadow: none;
            outline: none;
            animation: authIllustFloat 5.5s ease-in-out infinite;
        }
        @keyframes authIllustFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }
        .auth-illust-chart-layer {
            opacity: 0;
            transition: opacity 0.55s ease-in-out;
        }
        .auth-illust-chart-layer.is-visible {
            opacity: 1;
        }
        .auth-illust-bar__rect {
            transition: y 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                height 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                fill 0.45s ease;
        }
        .auth-illust-line__path {
            transition: d 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                stroke 0.45s ease;
        }
        .auth-illust-line__dot {
            transition: cx 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                cy 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                fill 0.45s ease;
        }
        .auth-illust-pie-segments {
            transition: opacity 0.3s ease;
        }
        .auth-illust-pie-segments path {
            transition: fill 0.4s ease;
            shape-rendering: geometricPrecision;
        }
        .auth-illust-coin {
            transform-box: fill-box;
            transform-origin: center;
            animation: authCoinFloat 3.4s ease-in-out infinite;
        }
        .auth-illust-coin--try { animation-delay: 0s; }
        .auth-illust-coin--usd { animation-delay: 0.9s; }
        .auth-illust-coin--eur { animation-delay: 1.6s; }
        @keyframes authCoinFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-9px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .auth-illustration,
            .auth-illust-coin {
                animation: none;
            }
            .auth-illust-bar__rect,
            .auth-illust-line__path,
            .auth-illust-line__dot,
            .auth-illust-pie-segments path,
            .auth-illust-chart-layer {
                transition: none;
            }
        }

        .auth-split__panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px 40px;
            background: linear-gradient(160deg, var(--navy-panel) 0%, var(--navy-mid) 50%, #0f2847 100%);
            position: relative;
        }
        .auth-split__panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 20% 0%, rgba(37, 99, 235, 0.08) 0%, transparent 55%);
            pointer-events: none;
        }

        .auth-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .auth-glass {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            transition:
                background 0.28s ease,
                border-color 0.28s ease,
                box-shadow 0.28s ease,
                transform 0.28s ease;
        }
        .auth-glass:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow:
                0 12px 40px rgba(15, 23, 42, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.06) inset,
                0 0 32px rgba(37, 99, 235, 0.12);
            transform: translateY(-3px);
        }

        .auth-card { padding: 32px 28px 28px; }

        .auth-card__title {
            margin: 0 0 6px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }
        .auth-card__subtitle {
            margin: 0 0 26px;
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.75);
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            font-size: 0.9375rem;
            font-family: inherit;
            color: #fff;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .form-group input::placeholder { color: rgba(148, 163, 184, 0.6); }
        .form-group input:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .form-group input:focus {
            outline: none;
            border-color: rgba(37, 99, 235, 0.6);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .password-input-wrap { position: relative; display: block; }
        .password-input-wrap input { padding-right: 46px; }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .password-toggle:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }
        .password-toggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.5);
        }
        .password-toggle__icon { display: none; flex-shrink: 0; }
        .password-toggle__icon--closed { display: block; }
        .password-toggle.is-revealed .password-toggle__icon--closed { display: none; }
        .password-toggle.is-revealed .password-toggle__icon--open { display: block; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 13px 20px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.1s ease, box-shadow 0.2s ease, background 0.15s ease;
        }
        .btn-primary {
            background: var(--blue);
            color: #fff;
            margin-top: 4px;
            box-shadow: 0 0 24px var(--blue-glow), 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .btn-primary:hover {
            background: #3b82f6;
            box-shadow: 0 0 32px var(--blue-glow), 0 6px 20px rgba(37, 99, 235, 0.45);
        }
        .btn-primary:active { transform: scale(0.99); }

        .auth-form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .auth-form-footer a {
            color: #60a5fa;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-form-footer a:hover { text-decoration: underline; }

        .auth-testimonial-carousel {
            width: 100%;
            text-align: left;
        }
        .auth-testimonial-track {
            overflow: hidden;
            width: 100%;
        }
        .auth-testimonial-slider {
            display: flex;
            width: 100%;
            transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
        .auth-testimonial-slide {
            flex: 0 0 100%;
            width: 100%;
            min-width: 100%;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            border: none;
        }
        @media (prefers-reduced-motion: reduce) {
            .auth-testimonial-slider {
                transition: none;
            }
        }
        .auth-testimonial-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
        }
        .auth-testimonial-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(191, 219, 254, 0.35);
            transition: background 0.5s ease-in-out, opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
        .auth-testimonial-dot.is-active {
            background: rgba(255, 255, 255, 0.95);
            transform: scale(1.15);
        }
        .auth-testimonial {
            margin: 0;
            padding: 0;
            border: none;
            width: 100%;
            text-align: left;
        }
        .auth-testimonial__quote {
            margin: 0 0 16px;
            font-size: 0.9375rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        .auth-testimonial__author {
            display: flex;
            align-items: center;
            gap: 12px;
            font-style: normal;
        }
        .auth-testimonial__avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .auth-testimonial__name {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            font-style: normal;
        }
        .auth-testimonial__role {
            display: block;
            font-size: 0.8125rem;
            color: rgba(191, 219, 254, 0.75);
            margin-top: 2px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.875rem;
            line-height: 1.45;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .alert-success {
            background: rgba(74, 222, 128, 0.1);
            color: var(--green);
            border: 1px solid rgba(74, 222, 128, 0.25);
        }
        .alert-error {
            background: rgba(248, 113, 113, 0.1);
            color: var(--red);
            border: 1px solid rgba(248, 113, 113, 0.25);
        }
        .text-danger {
            color: var(--red);
            font-size: 0.8125rem;
            margin-top: 6px;
        }

        .toast-container {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .toast {
            min-width: 240px;
            max-width: 360px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.875rem;
            backdrop-filter: blur(10px);
            background: rgba(15, 33, 55, 0.9);
            border: 1px solid var(--glass-border);
            color: var(--text);
            transform: translateY(12px);
            opacity: 0;
            animation: toastIn 0.2s ease forwards;
        }
        .toast.success { border-left: 3px solid var(--green); }
        .toast.error { border-left: 3px solid var(--red); }
        .toast.hide { animation: toastOut 0.2s ease forwards; }
        @keyframes toastIn { to { transform: translateY(0); opacity: 1; } }
        @keyframes toastOut { to { transform: translateY(12px); opacity: 0; } }

        @media (max-width: 959px) {
            .auth-split { flex-direction: column; }
            .auth-split__visual { display: flex; min-height: auto; }
            .auth-visual {
                min-height: auto;
                padding: 36px 24px 32px;
            }
            .auth-visual__inner { gap: 20px; }
            .auth-visual__brand { font-size: 1.65rem; }
            .auth-illustration { max-width: 300px; }
            .auth-visual:hover .auth-illustration { transform: none; }
            .auth-split__panel { flex: 1; }
        }
    </style>
    @stack('styles')
</head>
<body class="auth-page">
<div class="auth-split">
    <div class="auth-split__visual">
        @include('partials.auth-visual')
    </div>
    <div class="auth-split__panel">
        <div class="auth-panel">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $err) {{ $err }}<br> @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script>
(() => {
    const toastContainer = document.getElementById('toast-container');
    window.appToast = function(message, type = 'success') {
        if (!toastContainer || !message) return;
        const node = document.createElement('div');
        node.className = `toast ${type}`;
        node.textContent = message;
        toastContainer.appendChild(node);
        setTimeout(() => {
            node.classList.add('hide');
            setTimeout(() => node.remove(), 220);
        }, 2500);
    };
    const successAlert = document.querySelector('.alert-success');
    if (successAlert?.textContent?.trim()) window.appToast(successAlert.textContent.trim(), 'success');
    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert?.textContent?.trim()) window.appToast('Please check the form for errors.', 'error');
})();

function initPasswordToggles() {
    document.querySelectorAll('form').forEach(function (form) {
        const inputs = form.querySelectorAll('.password-input-wrap input');
        const buttons = form.querySelectorAll('[data-password-toggle]');
        if (!inputs.length || !buttons.length) return;

        function setRevealed(revealed) {
            inputs.forEach(function (input) {
                input.type = revealed ? 'text' : 'password';
            });
            buttons.forEach(function (btn) {
                btn.classList.toggle('is-revealed', revealed);
                btn.setAttribute('aria-label', revealed ? 'Hide password' : 'Show password');
            });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setRevealed(inputs[0].type === 'password');
            });
        });

        setRevealed(false);
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordToggles);
} else {
    initPasswordToggles();
}

(function initAuthIllustration() {
    const svg = document.querySelector('.auth-illustration');
    if (!svg) return;

    const barLayer = svg.querySelector('.auth-illust-bars');
    const lineLayer = svg.querySelector('.auth-illust-line');
    const linePathEl = svg.querySelector('.auth-illust-line__path');
    const lineDotEl = svg.querySelector('.auth-illust-line__dot');
    const pieSegments = svg.querySelector('.auth-illust-pie-segments');
    if (!barLayer || !lineLayer || !linePathEl || !lineDotEl || !pieSegments) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const BAR_X = [48, 90, 132, 174];
    const BAR_W = 30;
    const BASE_Y = 255;
    const LINE_X = [64, 108, 152, 196];
    const CHART_COLORS = ['#2563eb', '#60a5fa', '#34d399', '#93c5fd', '#f59e0b', '#a78bfa', '#f472b6', '#22d3ee'];
    const PIE_OUT = 50;
    const PIE_IN = 28;
    const PIE_COUNT = 4;

    let chartMode = 'bar';

    const shuffle = (arr) => {
        const copy = arr.slice();
        for (let i = copy.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copy[i], copy[j]] = [copy[j], copy[i]];
        }
        return copy;
    };

    const distinctColors = (count) => shuffle(CHART_COLORS).slice(0, count);

    const roundCoord = (value) => Math.round(value * 100) / 100;

    const polar = (radius, degrees) => {
        const rad = ((degrees - 90) * Math.PI) / 180;
        return [roundCoord(radius * Math.cos(rad)), roundCoord(radius * Math.sin(rad))];
    };

    const donutSlice = (startDeg, endDeg) => {
        const start = roundCoord(startDeg);
        const end = roundCoord(endDeg);
        const sweep = end - start;
        if (sweep <= 0) return '';
        const large = sweep > 180 ? 1 : 0;
        const [x1, y1] = polar(PIE_OUT, start);
        const [x2, y2] = polar(PIE_OUT, end);
        const [x3, y3] = polar(PIE_IN, end);
        const [x4, y4] = polar(PIE_IN, start);
        return 'M ' + x1 + ' ' + y1
            + ' A ' + PIE_OUT + ' ' + PIE_OUT + ' 0 ' + large + ' 1 ' + x2 + ' ' + y2
            + ' L ' + x3 + ' ' + y3
            + ' A ' + PIE_IN + ' ' + PIE_IN + ' 0 ' + large + ' 0 ' + x4 + ' ' + y4
            + ' Z';
    };

    const randomBars = () => {
        const colors = distinctColors(BAR_X.length);
        return BAR_X.map((x, i) => {
            const height = 52 + Math.random() * 108;
            return {
                x,
                y: BASE_Y - height,
                width: BAR_W,
                height,
                fill: colors[i],
            };
        });
    };

    const applyBars = (bars) => {
        barLayer.querySelectorAll('.auth-illust-bar').forEach((group, i) => {
            const rect = group.querySelector('.auth-illust-bar__rect');
            const bar = bars[i];
            if (!rect || !bar) return;
            rect.setAttribute('x', String(bar.x));
            rect.setAttribute('y', String(bar.y));
            rect.setAttribute('height', String(bar.height));
            rect.setAttribute('fill', bar.fill);
        });
    };

    const randomLine = () => LINE_X.map((x) => ({
        x,
        y: 118 + Math.random() * 102,
    }));

    const applyLine = (points) => {
        const colors = distinctColors(LINE_X.length);
        const path = points.map((p, i) => (i === 0 ? 'M' : 'L') + p.x + ' ' + p.y).join(' ');
        linePathEl.setAttribute('d', path);
        linePathEl.setAttribute('stroke', colors[0]);
        const last = points[points.length - 1];
        lineDotEl.setAttribute('cx', String(last.x));
        lineDotEl.setAttribute('cy', String(last.y));
        lineDotEl.setAttribute('fill', colors[colors.length - 1]);
    };

    const randomPie = () => {
        const values = Array.from({ length: PIE_COUNT }, () => 10 + Math.random() * 30);
        const total = values.reduce((sum, v) => sum + v, 0);
        const colors = distinctColors(PIE_COUNT);
        let angle = 0;

        return values.map((value, i) => {
            const isLast = i === values.length - 1;
            const start = angle;
            angle = isLast ? 360 : angle + (value / total) * 360;
            return {
                d: donutSlice(start, angle),
                fill: colors[i],
            };
        }).filter((segment) => segment.d !== '');
    };

    const renderPie = () => {
        const segments = randomPie();
        const applySegments = () => {
            let paths = pieSegments.querySelectorAll('path');

            if (paths.length !== segments.length) {
                pieSegments.innerHTML = segments.map(() => '<path opacity="0.95"></path>').join('');
                paths = pieSegments.querySelectorAll('path');
            }

            segments.forEach((segment, i) => {
                if (!paths[i]) return;
                paths[i].setAttribute('d', segment.d);
                paths[i].setAttribute('fill', segment.fill);
            });
        };

        if (reduceMotion) {
            applySegments();
            return;
        }

        pieSegments.style.opacity = '0.35';
        window.setTimeout(() => {
            applySegments();
            pieSegments.style.opacity = '1';
        }, 140);
    };

    const setChartMode = (mode) => {
        chartMode = mode;
        barLayer.classList.toggle('is-visible', mode === 'bar');
        lineLayer.classList.toggle('is-visible', mode === 'line');
    };

    const refreshChart = () => {
        if (chartMode === 'bar') applyBars(randomBars());
        else applyLine(randomLine());
    };

    applyBars(randomBars());
    applyLine(randomLine());
    renderPie();
    setChartMode('bar');

    if (reduceMotion) return;

    window.setInterval(refreshChart, 2800);
    window.setInterval(() => {
        setChartMode(chartMode === 'bar' ? 'line' : 'bar');
        refreshChart();
    }, 4500);
    window.setInterval(renderPie, 2600);
})();

(function initAuthTestimonialCarousel() {
    const carousel = document.querySelector('.auth-testimonial-carousel');
    if (!carousel) return;

    const slider = carousel.querySelector('.auth-testimonial-slider');
    const slides = Array.from(carousel.querySelectorAll('.auth-testimonial-slide'));
    const dots = Array.from(carousel.querySelectorAll('.auth-testimonial-dot'));
    if (!slider || slides.length < 2) return;

    let index = slides.findIndex((s) => s.classList.contains('is-active'));
    if (index < 0) index = 0;

    const intervalMs = Math.max(3000, parseInt(carousel.dataset.interval, 10) || 4200);
    const slideMs = 700;
    let timer = null;
    let transitioning = false;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const syncState = () => {
        slides.forEach((slide, i) => {
            const active = i === index;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    };

    const goTo = (nextIndex) => {
        const next = (nextIndex + slides.length) % slides.length;
        if (next === index) return;

        const wrapToStart = next === 0 && index === slides.length - 1;
        if (wrapToStart && !reduceMotion) {
            slider.style.transition = 'none';
            index = 0;
            slider.style.transform = 'translate3d(0, 0, 0)';
            syncState();
            void slider.offsetWidth;
            slider.style.transition = '';
            return;
        }

        index = next;
        slider.style.transform = 'translate3d(-' + (index * 100) + '%, 0, 0)';
        syncState();
    };

    const show = (nextIndex) => {
        if (transitioning) return;
        transitioning = true;
        goTo(nextIndex);
        window.setTimeout(() => {
            transitioning = false;
        }, reduceMotion ? 0 : slideMs);
    };

    slider.style.transform = 'translate3d(-' + (index * 100) + '%, 0, 0)';

    const start = () => {
        if (timer) return;
        timer = window.setInterval(() => show(index + 1), intervalMs);
    };

    const stop = () => {
        if (!timer) return;
        window.clearInterval(timer);
        timer = null;
    };

    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', start);

    if (!reduceMotion) {
        start();
    }
})();
</script>
@stack('scripts')
</body>
</html>
