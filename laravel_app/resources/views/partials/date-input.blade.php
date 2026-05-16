@php
    $id = $id ?? 'date';
    $name = $name ?? $id;
    $value = $value ?? '';
    $max = $max ?? null;
    $required = !empty($required);
    $inputClass = trim('date-input--picker ' . ($inputClass ?? ''));
@endphp
<div class="date-field">
    <input
        type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        class="{{ $inputClass }}"
        value="{{ $value }}"
        placeholder="Select date"
        lang="en"
        autocomplete="off"
        readonly
        @if($max) data-max="{{ $max }}" @endif
        @if($required) required @endif
    >
    <button type="button" class="date-field__trigger" aria-label="Open calendar" tabindex="-1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
        </svg>
    </button>
</div>
