<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>(function(){try{var t=localStorage.getItem('theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
    <title>@yield('title', 'Home') - {{ config('app.name') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap');
        :root {
            --bg: #e2e8f0;
            --surface: #ffffff;
            --surface2: #f1f5f9;
            --border: rgba(15, 23, 42, 0.08);
            --border2: rgba(15, 23, 42, 0.12);
            --acc: #2563eb;
            --acc2: #3b82f6;
            --acc-light: rgba(37, 99, 235, 0.1);
            --green: #16a34a;
            --green-light: rgba(22, 163, 74, 0.12);
            --red: #dc2626;
            --red-light: rgba(220, 38, 38, 0.1);
            --txt: #0f172a;
            --txt2: #334155;
            --muted: #64748b;
        }
        * { box-sizing: border-box; }

        body.auth-page {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            color: var(--txt);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            background:
                radial-gradient(900px 420px at 82% -6%, rgba(59, 130, 246, 0.14) 0%, transparent 72%),
                radial-gradient(680px 360px at 8% 8%, rgba(37, 99, 235, 0.07) 0%, transparent 76%),
                linear-gradient(180deg, #f4f6f8 0%, #e2e8f0 48%, #d5dde6 100%);
        }

        #auth-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .auth-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        body.auth-page .card {
            position: relative;
            z-index: 1;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 0;
        }

        h1 { margin: 0 0 14px; font-size: 24px; font-weight: 600; color: var(--txt); }

        .auth-typing-wrap {
            min-height: 3.25rem;
            margin-bottom: 1.15rem;
            text-align: center;
        }
        .auth-typing {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
            color: var(--txt2);
            letter-spacing: 0.02em;
            line-height: 1.45;
        }
        .auth-typing #auth-typing-text {
            color: var(--acc);
        }
        .auth-caret {
            display: inline-block;
            margin-left: 2px;
            color: var(--acc);
            font-weight: 300;
            animation: authCaretBlink 0.95s step-end infinite;
        }
        @keyframes authCaretBlink {
            50% { opacity: 0; }
        }

        .card {
            position: relative;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 16px 40px rgba(15, 23, 42, 0.08);
            padding: 20px 24px;
            overflow: hidden;
        }
        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 44px;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.06), rgba(255, 255, 255, 0));
            pointer-events: none;
        }

        .btn {
            border-radius: 10px;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(90deg, #1d4ed8, var(--acc2));
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
        }
        .btn-primary:hover {
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
            filter: brightness(1.04);
        }
        .btn-secondary {
            background: var(--surface2);
            border: 1px solid var(--border2);
            color: var(--txt2);
            font-weight: 500;
        }
        .btn-secondary:hover { background: rgba(37, 99, 235, 0.07); border-color: rgba(37, 99, 235, 0.35); color: var(--acc); }

        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--txt2);
        }
        .form-group input {
            background: var(--surface2);
            border: 1.5px solid var(--border2);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: var(--txt);
            width: 100%;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--acc);
        }
        .form-group input:focus-visible {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }
        .form-group input:focus:not(:focus-visible) {
            box-shadow: none;
        }

        .alert { padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.92rem; }
        .alert-success { background: var(--green-light); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.25); }
        .alert-error { background: var(--red-light); color: #b91c1c; border: 1px solid rgba(220, 38, 38, 0.22); }
        .text-danger { color: var(--red); font-size: 0.85rem; margin-top: 0.25rem; }

        .toast-container {
            position: fixed; right: 1rem; bottom: 1rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.6rem;
        }
        .toast {
            min-width: 240px; max-width: 360px;
            background: var(--surface); border-radius: 12px;
            border: 1px solid var(--border2);
            box-shadow: 0 10px 32px rgba(15, 23, 42, 0.1);
            padding: 0.72rem 0.9rem;
            color: var(--txt2);
            transform: translateY(24px); opacity: 0;
            animation: toastIn 0.2s ease forwards;
        }
        .toast.success { border-left: 4px solid var(--green); }
        .toast.error { border-left: 4px solid var(--red); }
        .toast.hide { animation: toastOut 0.2s ease forwards; }
        @keyframes toastIn { to { transform: translateY(0); opacity: 1; } }
        @keyframes toastOut { to { transform: translateY(24px); opacity: 0; } }

        [data-theme="dark"] {
            --bg: #0f172a;
            --surface: #1e293b;
            --surface2: #334155;
            --border: rgba(241, 245, 249, 0.08);
            --border2: rgba(241, 245, 249, 0.12);
            --txt: #f1f5f9;
            --txt2: #cbd5e1;
            --muted: #94a3b8;
            --acc-light: rgba(59, 130, 246, 0.18);
            --green-light: rgba(34, 197, 94, 0.16);
            --red-light: rgba(248, 113, 113, 0.14);
        }
        [data-theme="dark"] body.auth-page {
            background: #0f172a;
            color: var(--txt);
        }
        [data-theme="dark"] body.auth-page {
            background-image: none;
        }
        [data-theme="dark"] .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25), 0 16px 40px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .card::before {
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.1), transparent);
        }
        [data-theme="dark"] .auth-typing {
            color: var(--txt2);
        }
        [data-theme="dark"] .form-group input {
            background: var(--surface2);
            color: var(--txt);
            border-color: var(--border2);
        }
        [data-theme="dark"] .form-group label {
            color: var(--txt2);
        }
        [data-theme="dark"] .btn-secondary {
            background: var(--surface2);
            border-color: var(--border2);
            color: var(--txt2);
        }
        [data-theme="dark"] .alert-success {
            color: #86efac;
            border-color: rgba(34, 197, 94, 0.35);
        }
        [data-theme="dark"] .alert-error {
            color: #fca5a5;
            border-color: rgba(248, 113, 113, 0.35);
        }
        [data-theme="dark"] .toast {
            color: var(--txt2);
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.45);
        }
        .theme-icon--light { display: none !important; }
        .theme-icon--dark { display: block !important; }
        [data-theme="dark"] .theme-icon--dark { display: none !important; }
        [data-theme="dark"] .theme-icon--light { display: block !important; }
        .auth-theme-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 20;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            color: var(--muted);
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.1);
        }
        .auth-theme-toggle:hover {
            background: var(--acc-light);
            color: var(--acc);
        }
        [data-theme="dark"] .auth-theme-toggle {
            background: rgba(30, 41, 59, 0.92);
            color: #94a3b8;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .auth-theme-toggle:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }
        .auth-theme-toggle svg {
            display: block;
            width: 20px;
            height: 20px;
        }

    </style>
    @stack('styles')
</head>
<body class="auth-page">
<button type="button" class="auth-theme-toggle" data-theme-toggle title="Toggle theme" aria-label="Toggle theme">
    <svg class="theme-icon theme-icon--light" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
    <svg class="theme-icon theme-icon--dark" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
</button>
<canvas id="auth-canvas"></canvas>
<div class="auth-content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $err) {{ $err }}<br> @endforeach
        </div>
    @endif
    <div class="auth-typing-wrap" aria-live="polite">
        <p class="auth-typing" id="auth-typing-line">
            <span id="auth-typing-text"></span><span class="auth-caret" aria-hidden="true">|</span>
        </p>
    </div>
    @yield('content')
</div>

<div class="toast-container" id="toast-container"></div>
<script>
(() => {
    const canvas = document.getElementById('auth-canvas');
    if (!canvas || !canvas.getContext) return;
    const ctx = canvas.getContext('2d');
    /* Tutar / para — kucuk punto */
    const SYMBOL_POOL = [
        '$', '€', '£', '¥', '₺', '₿', '₹', '₽', '¤', '%',
        ',', '.', '/', '+', '−',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    ];
    /* Grafik emojileri (arka plan — kucuk punto) */
    const CHART_POOL = ['📊', '📈', '📉', '💹', '◔', '◕', '◐', '◑'];

    let symbols = [];

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function pickSymbol() {
        const chartChance = 0.45;
        if (Math.random() < chartChance) {
            return {
                kind: 'chart',
                symbol: CHART_POOL[Math.floor(Math.random() * CHART_POOL.length)],
                size: rand(20, 30),
                opacity: rand(0.06, 0.18),
            };
        }
        return {
            kind: 'text',
            symbol: SYMBOL_POOL[Math.floor(Math.random() * SYMBOL_POOL.length)],
            size: rand(12, 21),
            opacity: rand(0.08, 0.22),
        };
    }

    function buildSymbols() {
        symbols = [];
        const w = canvas.width;
        const h = canvas.height;
        for (let i = 0; i < 120; i++) {
            const p = pickSymbol();
            symbols.push({
                x: Math.random() * w,
                y: Math.random() * h,
                speed: rand(0.4, 1.2),
                size: p.size,
                opacity: p.opacity,
                symbol: p.symbol,
                kind: p.kind,
                drift: rand(-0.3, 0.3),
            });
        }
    }

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        buildSymbols();
    }

    function frame() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const w = canvas.width;
        const h = canvas.height;

        for (const s of symbols) {
            s.y += s.speed;
            s.x += s.drift;
            if (s.y > h + 30) {
                s.y = -30;
                s.x = Math.random() * w;
            }
            if (s.x < -20 || s.x > w + 20) {
                s.drift *= -1;
            }
        }

        for (const s of symbols) {
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font =
                s.kind === 'chart'
                    ? `${s.size}px "Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",system-ui,sans-serif`
                    : `${s.size}px ui-monospace,Consolas,monospace,sans-serif`;
            ctx.fillStyle = (function () {
                const dark = document.documentElement.getAttribute("data-theme") === "dark";
                const base = dark ? "148, 163, 184" : "37, 99, 235";
                return `rgba(${base}, ${s.opacity * 0.72})`;
            })();
            ctx.fillText(s.symbol, s.x, s.y);
        }

        const n = symbols.length;
        for (let i = 0; i < n; i++) {
            for (let j = i + 1; j < n; j++) {
                const a = symbols[i];
                const b = symbols[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const dist = Math.hypot(dx, dy);
                if (dist < 100) {
                    ctx.beginPath();
                    ctx.strokeStyle = document.documentElement.getAttribute("data-theme") === "dark"
                        ? "rgba(148, 163, 184, 0.08)"
                        : "rgba(37, 99, 235, 0.07)";
                    ctx.lineWidth = 0.5;
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(frame);
    }

    window.addEventListener('resize', resize);
    resize();
    requestAnimationFrame(frame);
})();

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
    if (errorAlert?.textContent?.trim()) window.appToast('Form error detected.', 'error');
})();

(() => {
    const el = document.getElementById('auth-typing-text');
    if (!el) return;
    const phrases = [
        'Know where every ₺ goes.',
        'Budget smarter, not harder.',
        'See spending at a glance.',
        'Your money, mapped clearly.',
        'Small habits. Big clarity.',
    ];
    let phraseIndex = 0;
    let charIndex = 0;
    let phase = 'typing';
    const typeDelay = 46;
    const deleteDelay = 26;
    const holdAfterType = 2400;
    const holdAfterClear = 360;

    function step() {
        const full = phrases[phraseIndex % phrases.length];
        if (phase === 'typing') {
            if (charIndex < full.length) {
                charIndex += 1;
                el.textContent = full.slice(0, charIndex);
                setTimeout(step, typeDelay);
            } else {
                phase = 'holding';
                setTimeout(step, holdAfterType);
            }
        } else if (phase === 'holding') {
            phase = 'deleting';
            step();
        } else if (phase === 'deleting') {
            if (charIndex > 0) {
                charIndex -= 1;
                el.textContent = full.slice(0, charIndex);
                setTimeout(step, deleteDelay);
            } else {
                phraseIndex += 1;
                phase = 'between';
                setTimeout(step, holdAfterClear);
            }
        } else {
            phase = 'typing';
            step();
        }
    }
    step();
})();

(function () {
    document.querySelectorAll("[data-theme-toggle]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const html = document.documentElement;
            if (html.getAttribute("data-theme") === "dark") {
                html.removeAttribute("data-theme");
                try { localStorage.setItem("theme", "light"); } catch (e) {}
            } else {
                html.setAttribute("data-theme", "dark");
                try { localStorage.setItem("theme", "dark"); } catch (e) {}
            }
            window.dispatchEvent(new CustomEvent("app-theme-changed"));
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
