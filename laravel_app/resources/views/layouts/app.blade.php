<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.default.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');
        :root {
            --font-num: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            /* Acik tema: beyaz + mavi iskelet, vurgular asagida */
            --bg: #e2e8f0;
            --bg2: #cbd5e1;
            --surface: #ffffff;
            --surface2: #f1f5f9;
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
        * { box-sizing: border-box; }
        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            color: var(--txt);
            min-height: 100vh;
            background:
                radial-gradient(900px 420px at 88% -6%, rgba(59, 130, 246, 0.12) 0%, transparent 70%),
                radial-gradient(720px 380px at 6% 4%, rgba(37, 99, 235, 0.06) 0%, transparent 74%),
                linear-gradient(180deg, #f4f6f8 0%, #e2e8f0 50%, #d5dde6 100%);
        }
        .mono, .currency-value, .number-value, .date-cell, .amount-cell, .cell-id {
            font-family: var(--font-num);
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
            letter-spacing: -0.01em;
        }
        .app-shell {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: row;
            min-height: 100vh;
        }
        .app-sidebar {
            width: 248px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: sticky;
            top: 0;
            align-self: flex-start;
            height: 100vh;
            padding: 24px 14px 20px;
            z-index: 100;
            background: linear-gradient(180deg, #1e3a5f 0%, #0d2137 55%, #0a1a2e 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.12);
        }
        .app-sidebar::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(120% 80% at 0% 0%, rgba(59, 130, 246, 0.14), transparent 55%);
            pointer-events: none;
        }
        .sidebar-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
        }
        .sidebar-wordmark {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 10px;
            background: rgba(29, 78, 216, 0.45);
            border: 1px solid rgba(147, 197, 253, 0.2);
            font-weight: 700;
            font-size: 22px;
            color: #ffffff;
            letter-spacing: 0.02em;
            line-height: 1;
            cursor: default;
            user-select: none;
        }
        .sidebar-nav {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
            margin-top: 70px;
            padding: 0 8px;
            width: 100%;
        }
        .sidebar-link {
            color: rgba(226, 232, 240, 0.78);
            font-size: 14px;
            font-weight: 500;
            padding: 11px 14px;
            border-radius: 10px;
            text-decoration: none;
            position: relative;
            transition: background 0.2s ease, color 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 200px;
            box-sizing: border-box;
        }
        .sidebar-link__icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.9;
        }
        .sidebar-link--active .sidebar-link__icon {
            opacity: 1;
        }
        .sidebar-link:hover {
            color: #fff;
            background: rgba(59, 130, 246, 0.18);
        }
        .sidebar-link--active {
            color: #fff;
            background: linear-gradient(90deg, rgba(29, 78, 216, 0.95), rgba(59, 130, 246, 0.85));
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.25);
        }
        .sidebar-bottom {
            position: relative;
            z-index: 1;
            margin-top: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 8px 10px 4px;
        }
        .sidebar-footer {
            width: 100%;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
        }
        .sidebar-user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            max-width: 100%;
            padding: 7px 14px;
            min-height: 0;
            box-sizing: border-box;
            border-radius: 10px;
            background: rgba(29, 78, 216, 0.55);
            border: 1px solid rgba(147, 197, 253, 0.25);
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.02em;
            white-space: nowrap;
            text-align: center;
        }
        .sidebar-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            gap: 4px;
            width: 100%;
            max-width: 200px;
        }
        .sidebar-actions form {
            margin: 0;
            width: 100%;
            display: flex;
        }
        .sidebar-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 9px 14px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: rgba(226, 232, 240, 0.75);
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .sidebar-icon-link__label {
            line-height: 1;
        }
        .sidebar-icon-link:hover {
            background: rgba(59, 130, 246, 0.22);
            color: #fff;
        }
        .sidebar-icon-link--logout {
            color: #fca5a5;
        }
        .sidebar-icon-link--logout:hover {
            background: rgba(220, 38, 38, 0.2);
            color: #fecaca;
        }
        .sidebar-icon-link svg {
            display: block;
            width: 20px;
            height: 20px;
        }
        .main {
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
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

        /* Custom selects — chevron only on <select>, not date inputs */
        .select-control,
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            background-color: var(--surface2);
        }
        select.select-control,
        .form-group select {
            padding-right: 2.75rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1.125rem;
        }
        input.select-control.date-input--picker {
            background-image: none;
        }
        .select-control:hover,
        .form-group select:hover {
            border-color: rgba(37, 99, 235, 0.35);
            background-color: #fff;
        }
        .select-control:focus,
        .form-group select:focus {
            background-color: #fff;
        }
        input.select-control.date-input--picker:focus {
            background-image: none;
        }
        .select-control option,
        .form-group select option {
            color: var(--txt);
            background: var(--surface);
        }
        .form-group select:required:invalid {
            color: var(--muted);
        }
        .form-group input[type="date"],
        .form-group input[type="datetime-local"],
        .date-input--picker {
            cursor: pointer;
        }
        .date-field {
            position: relative;
            display: block;
            width: 100%;
        }
        .date-field .date-input--picker {
            width: 100%;
            padding-right: 2.75rem;
        }
        .date-field .date-input--picker::-webkit-calendar-picker-indicator {
            display: none;
            -webkit-appearance: none;
        }
        .date-field .date-input--picker::-webkit-inner-spin-button,
        .date-field .date-input--picker::-webkit-clear-button {
            display: none;
            -webkit-appearance: none;
        }
        .date-field .date-input--picker::-moz-focus-inner {
            border: 0;
        }
        .date-field__trigger {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.35rem;
            height: 1.35rem;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .date-field__trigger:hover {
            color: var(--acc);
        }
        .date-field__trigger svg {
            width: 1.125rem;
            height: 1.125rem;
            display: block;
        }
        .date-field .flatpickr-input.form-control,
        .date-field .flatpickr-input {
            padding-right: 2.75rem;
        }
        .flatpickr-calendar {
            border-radius: 14px;
            border: 1px solid var(--border2);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
            font-family: 'DM Sans', system-ui, sans-serif;
            overflow: hidden;
        }
        .flatpickr-months .flatpickr-month {
            background: var(--surface2);
            color: var(--txt);
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            font-weight: 600;
            color: var(--txt);
        }
        .flatpickr-weekdays {
            background: var(--surface2);
        }
        span.flatpickr-weekday {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.72rem;
        }
        .flatpickr-day {
            border-radius: 8px;
            color: var(--txt);
        }
        .flatpickr-day.today {
            border-color: var(--acc);
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: var(--acc);
            border-color: var(--acc);
        }
        .flatpickr-day:hover {
            background: var(--acc-light);
        }

        /* Tom Select — modern category / dropdown menus */
        .ts-wrapper {
            width: 100%;
        }
        .ts-wrapper.single .ts-control {
            border: 1.5px solid var(--border2);
            border-radius: 10px;
            padding: 10px 36px 10px 14px;
            min-height: 42px;
            background: var(--surface2);
            color: var(--txt);
            font-size: 14px;
            font-family: 'DM Sans', system-ui, sans-serif;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        .ts-wrapper.single .ts-control:hover {
            border-color: rgba(37, 99, 235, 0.35);
            background: #fff;
        }
        .ts-wrapper.focus .ts-control {
            border-color: var(--acc);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }
        .ts-wrapper.single .ts-control .item {
            color: var(--txt);
        }
        .ts-wrapper.single .ts-control input {
            font-size: 14px;
            color: var(--txt);
        }
        .ts-dropdown {
            border: 1px solid var(--border2);
            border-radius: 12px;
            margin-top: 6px;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
            font-family: 'DM Sans', system-ui, sans-serif;
            z-index: 200;
        }
        .ts-dropdown .option {
            padding: 10px 14px;
            color: var(--txt);
        }
        .ts-dropdown .option.active {
            background: var(--acc-light);
            color: var(--acc);
        }
        .ts-dropdown .option:hover,
        .ts-dropdown .option.selected {
            background: rgba(37, 99, 235, 0.1);
            color: var(--acc);
        }
        .ts-dropdown .no-results {
            padding: 10px 14px;
            color: var(--muted);
        }
        .expenses-filter__field .ts-wrapper.single .ts-control,
        .fixed-template-row .ts-wrapper.single .ts-control {
            min-height: 38px;
            padding: 8px 34px 8px 12px;
            font-size: 0.9375rem;
        }

        .filter-toolbar {
            padding: 0.875rem 1.25rem !important;
            margin-bottom: 1rem;
        }
        .filter-toolbar__form {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            flex-wrap: wrap;
        }
        .filter-toolbar__label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            flex-shrink: 0;
        }
        .filter-toolbar__label svg {
            width: 1.125rem;
            height: 1.125rem;
            color: var(--acc);
            flex-shrink: 0;
        }
        .select-field {
            position: relative;
            display: inline-flex;
            min-width: 11.5rem;
        }
        .select-field--compact .select-control {
            min-width: 11.5rem;
            padding: 0.625rem 2.5rem 0.625rem 1rem;
            font-size: 0.9375rem;
            font-weight: 500;
            font-family: var(--font-num);
            font-variant-numeric: tabular-nums;
            border: 1.5px solid var(--border2);
            border-radius: 10px;
            color: var(--txt);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.85);
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        .select-field--compact .select-control:focus {
            outline: none;
            border-color: var(--acc);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }
        .filter-toolbar--expenses {
            padding: 1rem 1.25rem !important;
        }
        .filter-toolbar--sidebar .expenses-filter__heading {
            margin: 0 0 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }
        .expenses-filter__form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr));
            gap: 0.875rem 1rem;
            align-items: end;
        }
        .expenses-filter__field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--txt2);
        }
        .expenses-filter__field .select-control {
            width: 100%;
            min-width: 0;
            padding: 0.625rem 2.25rem 0.625rem 0.875rem;
            font-size: 0.9375rem;
            border: 1.5px solid var(--border2);
            border-radius: 10px;
            background-color: var(--surface2);
            color: var(--txt);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }
        .expenses-filter__field .date-field .select-control {
            font-family: var(--font-num);
            font-variant-numeric: tabular-nums;
        }
        .expenses-filter__field--month {
            grid-column: 1 / -1;
        }
        @media (min-width: 640px) {
            .expenses-filter__field--month {
                grid-column: span 2;
            }
        }
        .expenses-filter__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
        @media (min-width: 720px) {
            .expenses-filter__actions {
                justify-content: flex-end;
            }
        }

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
            background: var(--bg2);
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
            .app-shell { flex-direction: column; }
            .app-sidebar {
                width: 100%;
                height: auto;
                min-height: 0;
                position: relative;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                padding: 12px 14px;
                gap: 8px;
            }
            .sidebar-nav {
                flex: 1 1 100%;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 0;
                order: 3;
                gap: 6px;
            }
            .sidebar-link {
                padding: 8px 12px;
                font-size: 13px;
                width: auto;
                max-width: none;
            }
            .sidebar-bottom {
                margin-top: 0;
                margin-left: auto;
                order: 2;
                width: auto;
                flex-direction: column;
                gap: 8px;
                padding: 0;
            }
            .sidebar-footer {
                padding-top: 10px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            .sidebar-user-avatar {
                padding: 6px 12px;
                font-size: 13px;
            }
            .main-inner { padding: 20px 14px; }
        }
    </style>
    @stack('styles')
</head>
<body data-currency-symbol="{{ $currencySymbol ?? '₺' }}">
        @if(session('user_id'))
        <div class="app-shell">
            @php
                $fullName = auth()->check() ? auth()->user()->name : (string) session('user_name', '');
                $nameParts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
                $firstNameOnly = $nameParts[0] ?? '';
                $displayInBox = $firstNameOnly !== '' ? $firstNameOnly : 'User';
            @endphp
            <aside class="app-sidebar" aria-label="Main navigation">
                <div class="sidebar-header">
                    <div class="sidebar-wordmark">FinTrack</div>
                </div>
                <nav class="sidebar-nav">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('expenses.index') }}"
                       class="sidebar-link {{ request()->routeIs('expenses.index') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>All Expenses</span>
                    </a>
                    <a href="{{ route('expenses.create') }}"
                       class="sidebar-link {{ request()->routeIs('expenses.create') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <span>Add Expense</span>
                    </a>
                    <a href="{{ route('expenses.receipt-scan') }}"
                       class="sidebar-link {{ request()->routeIs('expenses.receipt-scan*') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V9.75M3 9.75l1.72-3.24A2.25 2.25 0 016.36 5.25h11.28a2.25 2.25 0 012.64 1.26L21 9.75M12 13.5a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z"/></svg>
                        <span>Receipt Scan</span>
                    </a>
                    <a href="{{ route('charts') }}"
                       class="sidebar-link {{ request()->routeIs('charts') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        <span>Analytics</span>
                    </a>
                    <a href="{{ route('reports.index') }}"
                       class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link--active' : '' }}">
                        <svg class="sidebar-link__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Reports</span>
                    </a>
                </nav>
                <div class="sidebar-bottom">
                    <div class="sidebar-actions">
                        <a href="{{ route('profile.show') }}" class="sidebar-icon-link" title="Settings" aria-label="Settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span class="sidebar-icon-link__label">Settings</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="sidebar-icon-link sidebar-icon-link--logout" title="Log out" aria-label="Log out">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span class="sidebar-icon-link__label">Log out</span>
                            </button>
                        </form>
                    </div>
                    <div class="sidebar-footer">
                        <div class="sidebar-user-avatar">{{ $displayInBox }}</div>
                    </div>
                </div>
            </aside>

            <main class="main">
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
            </main>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script>
        (() => {
            window.initAppSelects = function(root = document) {
                if (typeof TomSelect === "undefined") return;
                root.querySelectorAll("select.select-enhanced").forEach((el) => {
                    if (el.tomselect) return;
                    const config = {
                        allowEmptyOption: true,
                        create: false,
                        maxOptions: 100,
                        closeAfterSelect: true,
                        controlInput: null,
                        dropdownParent: "body",
                    };
                    if (el.dataset.submitOnChange === "true") {
                        config.onChange = () => {
                            if (el.form) {
                                if (typeof el.form.requestSubmit === "function") {
                                    el.form.requestSubmit();
                                } else {
                                    el.form.submit();
                                }
                            }
                        };
                    }
                    new TomSelect(el, config);
                });
            };
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

            if (typeof flatpickr !== "undefined") {
                document.querySelectorAll(".date-field").forEach((field) => {
                    const input = field.querySelector(".date-input--picker");
                    const trigger = field.querySelector(".date-field__trigger");
                    if (!input || input._flatpickr) return;

                    const maxDate = input.dataset.max || "today";
                    const fp = flatpickr(input, {
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "M j, Y",
                        maxDate,
                        disableMobile: true,
                        allowInput: false,
                        locale: { firstDayOfWeek: 1 },
                    });

                    const openPicker = () => fp.open();
                    fp.altInput?.addEventListener("click", openPicker);
                    trigger?.addEventListener("click", (e) => {
                        e.preventDefault();
                        openPicker();
                    });
                });
            }

            window.initAppSelects();
        })();
    </script>

    @stack('scripts')
</body>
</html>
