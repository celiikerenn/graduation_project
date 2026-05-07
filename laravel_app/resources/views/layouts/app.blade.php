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
            /* Acik tema: beyaz + mavi iskelet, vurgular asagida */
            --bg: #f1f5f9;
            --bg2: #e2e8f0;
            --surface: #ffffff;
            --surface2: #f8fafc;
            --border: rgba(15, 23, 42, 0.08);
            --border2: rgba(15, 23, 42, 0.12);
            --acc: #2563eb;
            --acc2: #3b82f6;
            --acc-light: rgba(37, 99, 235, 0.1);
            --acc-glow: rgba(37, 99, 235, 0.18);
            --green: #16a34a;
            --green-light: rgba(22, 163, 74, 0.12);
            --amber: #d97706;
            --amber-light: rgba(217, 119, 6, 0.14);
            --red: #dc2626;
            --red-light: rgba(220, 38, 38, 0.1);
            --txt: #0f172a;
            --txt2: #334155;
            --muted: #64748b;
            /* Harcama kategorisi — Analytics grafikleri ve expense listesi rozetleri (tek kaynak) */
            --cat-food: #f59e0b;
            --cat-transport: #3b82f6;
            --cat-utilities: #8b5cf6;
            --cat-grocery: #10b981;
            --cat-groceries: #10b981;
            --cat-health: #ec4899;
            --cat-entertainment: #f97316;
            --cat-education: #06b6d4;
            --cat-clothing: #a78bfa;
            --cat-rent: #ef4444;
            --cat-other: #6b7280;
            --cat-fb-0: #F472B6;
            --cat-fb-1: #84CC16;
        }
        [data-theme="dark"] {
            --bg: #0f172a;
            --bg2: #1e293b;
            --surface: #1e293b;
            --surface2: #334155;
            --border: rgba(241, 245, 249, 0.08);
            --border2: rgba(241, 245, 249, 0.12);
            --txt: #f1f5f9;
            --txt2: #cbd5e1;
            --muted: #94a3b8;
            --acc-light: rgba(59, 130, 246, 0.18);
            --acc-glow: rgba(59, 130, 246, 0.22);
            --green-light: rgba(34, 197, 94, 0.16);
            --red-light: rgba(248, 113, 113, 0.14);
            --amber-light: rgba(251, 191, 36, 0.14);
        }
        [data-theme="dark"] body {
            background: #0f172a;
            color: var(--txt);
        }
        [data-theme="dark"] .topbar {
            background: rgba(30, 41, 59, 0.94);
            border-bottom-color: var(--border2);
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.4) inset, 0 8px 28px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .topbar-nav {
            background: rgba(51, 65, 85, 0.96);
            border-color: var(--border2);
        }
        [data-theme="dark"] .sidebar-link:hover {
            background: var(--acc-light);
        }
        [data-theme="dark"] .sidebar-user-avatar {
            background: #1d4ed8;
            border-color: #2563eb;
        }
        [data-theme="dark"] .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2), 0 12px 32px rgba(0, 0, 0, 0.25);
        }
        [data-theme="dark"] .card::before {
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08), transparent);
        }
        [data-theme="dark"] .main-inner > h1 {
            text-shadow: none;
        }
        [data-theme="dark"] .expenses-table thead {
            background: var(--surface2);
        }
        [data-theme="dark"] .expenses-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        [data-theme="dark"] .budget-bar {
            background: #334155;
            border-color: var(--border2);
        }
        [data-theme="dark"] .empty-state {
            background: rgba(59, 130, 246, 0.06);
            border-color: var(--border2);
        }
        [data-theme="dark"] .expenses-table-wrap {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.22), 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        [data-theme="dark"] .alert-success {
            color: #86efac;
            border-color: rgba(34, 197, 94, 0.35);
        }
        [data-theme="dark"] .alert-error {
            color: #fca5a5;
            border-color: rgba(248, 113, 113, 0.35);
        }
        [data-theme="dark"] .text-danger {
            color: #f87171;
        }
        [data-theme="dark"] .toast {
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.45);
        }
        .theme-icon--light { display: none !important; }
        .theme-icon--dark { display: block !important; }
        [data-theme="dark"] .theme-icon--dark { display: none !important; }
        [data-theme="dark"] .theme-icon--light { display: block !important; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            color: var(--txt);
            min-height: 100vh;
            background:
                radial-gradient(900px 420px at 88% -6%, rgba(59, 130, 246, 0.14) 0%, transparent 70%),
                radial-gradient(720px 380px at 6% 4%, rgba(37, 99, 235, 0.07) 0%, transparent 74%),
                linear-gradient(180deg, #ffffff 0%, #f1f5f9 50%, #e8eef5 100%);
        }
        .mono, .currency-value, .number-value, .date-cell, .amount-cell {
            font-family: 'DM Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .app-shell, .main { min-height: 100vh; display: flex; flex-direction: column; }
        #app-bg-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .app-shell {
            position: relative;
            z-index: 1;
        }
        .topbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border2);
            padding: 0 32px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.95) inset, 0 8px 28px rgba(15, 23, 42, 0.06);
        }
        .topbar::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.35), transparent);
            pointer-events: none;
        }
        .topbar::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(120% 180% at 50% -100%, rgba(59, 130, 246, 0.06), transparent 58%);
            pointer-events: none;
        }
        .topbar-left { display:flex; align-items:center; gap: 12px; }
        .topbar-right { display:flex; align-items:center; gap: 10px; color: var(--txt2); }
        .sidebar-header { display:flex; align-items:center; gap:8px; }
        .sidebar-wordmark {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 10px;
            background: #1d4ed8;
            border: 1px solid #1e40af;
        }
        .topbar-nav {
            display:flex;
            align-items:center;
            gap: 10px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(241, 245, 249, 0.95);
            border: 1px solid var(--border2);
            border-radius: 12px;
            padding: 4px 6px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }
        .sidebar-link {
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            transition: all 0.22s ease;
        }
        .sidebar-link:hover {
            color: var(--acc);
            background: var(--acc-light);
            transform: translateY(-1px);
        }
        .sidebar-link::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 4px;
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--acc), var(--acc2));
            transform: translateX(-50%);
            transition: width 0.22s ease;
        }
        .sidebar-link:hover::after { width: 58%; }
        .sidebar-link--active {
            color: #fff;
            background: linear-gradient(90deg, #1d4ed8, #3b82f6);
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
        }
        .sidebar-link--active::after { display: none; }
        .sidebar-user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            min-height: 44px;
            max-width: 220px;
            box-sizing: border-box;
            border-radius: 12px;
            background: #1d4ed8;
            border: 1px solid #1e40af;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topbar-user-wrap {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .topbar-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .topbar-icon-link:hover {
            background: var(--acc-light);
            color: var(--acc);
        }
        .topbar-icon-link--logout {
            color: #dc2626;
        }
        .topbar-icon-link--logout:hover {
            background: rgba(220, 38, 38, 0.14);
            color: #b91c1c;
        }
        .topbar-icon-link svg {
            display: block;
            width: 20px;
            height: 20px;
        }
        .sidebar-footer { position: relative; }
        .sidebar-user-trigger { cursor: default; }
        .sidebar-user-dropdown {
            position:absolute; right:0; top:2.6rem; min-width: 200px;
            background: var(--surface); border:1px solid var(--border2); border-radius: 12px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.12);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
        }
        .sidebar-user-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .sidebar-user-dropdown-link {
            display:block; width:100%; text-align:left; border:0; background:transparent; cursor:pointer;
            padding: 8px 10px; border-radius: 8px; color: var(--txt2); text-decoration:none; font-family: inherit;
        }
        .sidebar-user-dropdown-link:hover { background: var(--surface2); color: var(--txt); }

        .main-inner {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 32px 24px;
        }
        h1 { margin: 0 0 14px; font-size: 24px; font-weight: 600; color: var(--txt); }
        p { font-size: 14px; color: var(--txt2); }
        .section-label {
            font-size: 11px; font-weight: 600; color: var(--muted);
            letter-spacing: 0.08em; text-transform: uppercase;
        }
        .card {
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border2);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 20px 24px;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 44px;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.05), rgba(255, 255, 255, 0));
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
        .btn-primary:active { transform: scale(0.97); }
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
        .form-group input,
        .form-group select,
        .form-group textarea {
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
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--acc);
        }
        .form-group input:focus-visible,
        .form-group select:focus-visible,
        .form-group textarea:focus-visible {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }
        .form-group input:focus:not(:focus-visible),
        .form-group select:focus:not(:focus-visible),
        .form-group textarea:focus:not(:focus-visible) {
            box-shadow: none;
        }
        .form-group textarea { min-height: 90px; resize: vertical; }

        .alert { padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.92rem; }
        .alert-success { background: var(--green-light); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.25); }
        .alert-error { background: var(--red-light); color: #b91c1c; border: 1px solid rgba(220, 38, 38, 0.22); }
        .text-danger { color: var(--red); font-size: 0.85rem; margin-top: 0.25rem; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--border); }
        th {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: var(--surface2);
        }
        td { font-size: 13px; color: var(--txt); font-weight: 400; }
        .text-right { text-align: right; }

        tbody tr {
            transition: background-color 0.14s ease;
        }
        tbody tr:hover {
            background: rgba(37, 99, 235, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 2.25rem 1.5rem 2.5rem;
            border: 1px dashed var(--border2);
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.04);
            max-width: 420px;
            margin-inline: auto;
        }
        .empty-state--wide { max-width: none; }
        .empty-state__icon {
            font-size: 2.75rem;
            line-height: 1;
            margin-bottom: 0.75rem;
            opacity: 0.9;
            filter: grayscale(0.15);
        }
        .empty-state__title {
            font-weight: 600;
            font-size: 1.05rem;
            margin: 0 0 0.4rem;
            color: var(--txt);
        }
        .empty-state__text {
            margin: 0;
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.5;
            max-width: 24rem;
            margin-inline: auto;
        }
        .empty-state__text a { color: var(--acc); font-weight: 500; }

        .badge-category {
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            -webkit-font-smoothing: antialiased;
        }
        .badge-food { background: color-mix(in srgb, var(--cat-food) 38%, transparent); color: var(--cat-food); border: 1px solid color-mix(in srgb, var(--cat-food) 62%, transparent); }
        .badge-transport { background: color-mix(in srgb, var(--cat-transport) 38%, transparent); color: var(--cat-transport); border: 1px solid color-mix(in srgb, var(--cat-transport) 62%, transparent); }
        .badge-bills { background: color-mix(in srgb, var(--cat-utilities) 38%, transparent); color: var(--cat-utilities); border: 1px solid color-mix(in srgb, var(--cat-utilities) 62%, transparent); }
        .badge-shopping { background: color-mix(in srgb, var(--cat-groceries) 38%, transparent); color: var(--cat-groceries); border: 1px solid color-mix(in srgb, var(--cat-groceries) 62%, transparent); }
        .badge-health { background: color-mix(in srgb, var(--cat-health) 38%, transparent); color: var(--cat-health); border: 1px solid color-mix(in srgb, var(--cat-health) 62%, transparent); }
        .badge-entertainment { background: color-mix(in srgb, var(--cat-entertainment) 38%, transparent); color: var(--cat-entertainment); border: 1px solid color-mix(in srgb, var(--cat-entertainment) 62%, transparent); }
        .badge-education { background: color-mix(in srgb, var(--cat-education) 38%, transparent); color: var(--cat-education); border: 1px solid color-mix(in srgb, var(--cat-education) 62%, transparent); }
        .badge-clothing { background: color-mix(in srgb, var(--cat-clothing) 38%, transparent); color: var(--cat-clothing); border: 1px solid color-mix(in srgb, var(--cat-clothing) 62%, transparent); }
        .badge-other { background: color-mix(in srgb, var(--cat-other) 34%, transparent); color: var(--cat-other); border: 1px solid color-mix(in srgb, var(--cat-other) 58%, transparent); }
        .badge-rent { background: color-mix(in srgb, var(--cat-rent) 38%, transparent); color: var(--cat-rent); border: 1px solid color-mix(in srgb, var(--cat-rent) 62%, transparent); }

        .main-inner > h1 {
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .budget-bar {
            height: 9px;
            border-radius: 99px;
            background: #e2e8f0;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .budget-bar--warn {
            border-color: rgba(245, 158, 11, 0.42);
            box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.12), inset 0 0 12px rgba(234, 88, 12, 0.06);
        }
        .budget-bar--over {
            border-color: rgba(248, 113, 113, 0.48);
            box-shadow: 0 0 0 1px rgba(248, 113, 113, 0.15), inset 0 0 12px rgba(185, 28, 28, 0.12);
        }
        .budget-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #1e40af, #93c5fd);
            transition: width 1.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
            width: 0;
        }

        .enter-item { opacity: 0; transform: translateY(14px); animation: fadeUpIn 0.4s ease-out forwards; }
        @keyframes fadeUpIn { to { opacity: 1; transform: translateY(0); } }

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
        .toast.warning { border-left: 4px solid var(--amber); }
        .toast.error { border-left: 4px solid var(--red); }
        .toast.hide { animation: toastOut 0.2s ease forwards; }
        @keyframes toastIn { to { transform: translateY(0); opacity: 1; } }
        @keyframes toastOut { to { transform: translateY(24px); opacity: 0; } }

        @media (max-width: 768px) {
            .topbar { padding: 0 12px; height: auto; min-height: 56px; flex-wrap: wrap; gap: 8px; }
            .topbar-left { width: 100%; flex-wrap: wrap; }
            .topbar-nav {
                position: static;
                transform: none;
                width: 100%;
                overflow-x: auto;
                padding-bottom: 4px;
                background: transparent;
                border: 0;
                box-shadow: none;
                padding-left: 0;
                padding-right: 0;
            }
            .main-inner { padding: 20px 14px; }
        }
    </style>
    @stack('styles')
</head>
<body data-currency-symbol="{{ $currencySymbol ?? '₺' }}">
    @if(session('user_id'))
        <canvas id="app-bg-canvas" aria-hidden="true"></canvas>
        <div class="app-shell">
            <div class="main">
                <header class="topbar">
                    <div class="topbar-left">
                        <div class="sidebar-header" style="margin-bottom:0;">
                            <div class="sidebar-wordmark">
                                <span style="font-weight: 700; font-size: 22px; color: #ffffff;">Track</span><span style="font-weight: 700; font-size: 22px; color: #bfdbfe;">ly</span>
                            </div>
                        </div>
                        <nav class="topbar-nav">
                            <a href="{{ route('dashboard') }}"
                               class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link--active' : '' }}">
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('expenses.index') }}"
                               class="sidebar-link {{ request()->routeIs('expenses.index') ? 'sidebar-link--active' : '' }}">
                                <span>All Expenses</span>
                            </a>
                            <a href="{{ route('expenses.create') }}"
                               class="sidebar-link {{ request()->routeIs('expenses.create') ? 'sidebar-link--active' : '' }}">
                                <span>Add Expense</span>
                            </a>
                            <a href="{{ route('charts') }}"
                               class="sidebar-link {{ request()->routeIs('charts') ? 'sidebar-link--active' : '' }}">
                                <span>Analytics</span>
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link--active' : '' }}">
                                <span>Reports</span>
                            </a>
                        </nav>
                    </div>
                    <div class="topbar-right">
                        @php
                            $fullName = auth()->check() ? auth()->user()->name : (string) session('user_name', '');
                            $nameParts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
                            $firstNameOnly = $nameParts[0] ?? '';
                            $displayInBox = $firstNameOnly !== '' ? $firstNameOnly : 'User';
                        @endphp
                        <div class="topbar-user-wrap">
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;display:inline-flex;">
                                @csrf
                                <button type="submit" class="topbar-icon-link topbar-icon-link--logout" title="Sign out" aria-label="Sign out">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                </button>
                            </form>
                            <a href="{{ route('profile.show') }}" class="topbar-icon-link" title="Settings" aria-label="Settings">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </a>
                            <button type="button" class="topbar-icon-link" id="theme-toggle" data-theme-toggle title="Toggle theme" aria-label="Toggle theme">
                                <svg class="theme-icon theme-icon--light" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                <svg class="theme-icon theme-icon--dark" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            </button>
                            <div class="sidebar-user-avatar">
                                {{ $displayInBox }}
                            </div>
                        </div>
                    </div>
                </header>

                <div class="main-inner">
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
    @else
        <div class="main" style="margin-left:0; background:var(--bg);">
            <div class="main-inner" style="max-width: 980px; margin: 0 auto;">
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
    @endif

    <div class="toast-container" id="toast-container"></div>
    <script>
        (() => {
            const toastContainer = document.getElementById("toast-container");
            window.appToast = function(message, type = "success") {
                if (!toastContainer || !message) return;
                const node = document.createElement("div");
                node.className = `toast ${type}`;
                node.textContent = message;
                toastContainer.appendChild(node);
                setTimeout(() => {
                    node.classList.add("hide");
                    setTimeout(() => node.remove(), 220);
                }, 2500);
            };
            const successAlert = document.querySelector(".alert-success");
            if (successAlert?.textContent?.trim()) window.appToast(successAlert.textContent.trim(), "success");
            const errorAlert = document.querySelector(".alert-error");
            if (errorAlert?.textContent?.trim()) window.appToast("Form error detected.", "error");

            const staggerTargets = Array.from(document.querySelectorAll(".main-inner > h1, .main-inner > p, .main-inner .card, .main-inner form, .main-inner table"));
            staggerTargets.forEach((el, i) => {
                el.classList.add("enter-item");
                el.style.animationDelay = `${(i + 1) * 0.06}s`;
            });
        })();

        /* Auth sayfasindaki sembol yagmuru: cok seyrek, yavas, dusuk kontrast; cizgi yok; reduce-motion'da kapali */
        (() => {
            if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
            const canvas = document.getElementById("app-bg-canvas");
            if (!canvas || !canvas.getContext) return;
            const ctx = canvas.getContext("2d");
            const SYMBOL_POOL = ["$", "€", "£", "¥", "₺", "%", "↑", "↓", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
            let symbols = [];

            function rand(min, max) {
                return min + Math.random() * (max - min);
            }

            function buildSymbols() {
                symbols = [];
                const w = canvas.width;
                const h = canvas.height;
                const count = Math.min(22, Math.max(14, Math.floor((w * h) / 95000)));
                for (let i = 0; i < count; i++) {
                    symbols.push({
                        x: Math.random() * w,
                        y: Math.random() * h,
                        speed: rand(0.05, 0.16),
                        size: rand(12, 17),
                        opacity: rand(0.045, 0.11),
                        symbol: SYMBOL_POOL[Math.floor(Math.random() * SYMBOL_POOL.length)],
                        drift: rand(-0.1, 0.1),
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
                    if (s.y > h + 24) {
                        s.y = -24;
                        s.x = Math.random() * w;
                    }
                    if (s.x < -16 || s.x > w + 16) {
                        s.drift *= -1;
                    }
                }

                for (const s of symbols) {
                    ctx.font = `${s.size}px monospace`;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    const dark = document.documentElement.getAttribute("data-theme") === "dark";
                    ctx.fillStyle = dark
                        ? `rgba(148, 163, 184, ${s.opacity * 0.85})`
                        : `rgba(37, 99, 235, ${s.opacity * 0.95})`;
                    ctx.fillText(s.symbol, s.x, s.y);
                }

                requestAnimationFrame(frame);
            }

            window.addEventListener("resize", resize);
            resize();
            requestAnimationFrame(frame);
        })();
    </script>

    <script>
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
