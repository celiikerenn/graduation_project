@extends('layouts.app')

@section('title', 'Receipt Scan')

@section('content')
<h1>Receipt Scan</h1>
<p style="margin-top:-0.35rem; margin-bottom:0.75rem; color:var(--muted); font-size:0.9rem;">
    Upload a receipt photo. OCR reads amount, date and category — you confirm before saving.
</p>

@include('partials.ai-insights', ['insights' => $aiInsights ?? []])

@php
    $confidence = $scan['confidence'] ?? 'low';
    $confidenceLabel = match($confidence) {
        'high' => 'High confidence',
        'medium' => 'Medium confidence',
        default => 'Low confidence — check all fields',
    };
    $hasScan = !empty($scan);
@endphp

<div class="receipt-scan-layout">
    <div class="receipt-scan-col">
        <div class="card receipt-scan-card">
            <div class="receipt-scan-card__inner">
                <label class="receipt-scan-card__label" for="receipt">Receipt image</label>

                @if($hasScan && !empty($receiptPreview['data']))
                <div class="receipt-scan-form__main">
                    <div id="receipt-preview-wrap" style="margin-top:0.35rem;">
                        <img
                            id="receipt-preview"
                            src="data:{{ $receiptPreview['mime'] ?? 'image/jpeg' }};base64,{{ $receiptPreview['data'] }}"
                            alt="Scanned receipt"
                            style="max-width:100%; max-height:320px; border-radius:12px; border:1px solid var(--border2); display:block;"
                        >
                    </div>
                </div>
                <form method="POST" action="{{ route('expenses.receipt-scan.discard') }}" class="receipt-scan-card__footer">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Scan another receipt</button>
                </form>
                @else
                <form method="POST" action="{{ route('expenses.receipt-scan.store') }}" enctype="multipart/form-data" id="receipt-scan-form" class="receipt-scan-form">
                    @csrf
                    <div class="receipt-scan-form__main">
                    <input
                        type="file"
                        id="receipt"
                        name="receipt"
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        required
                        class="receipt-scan-file-input"
                    >
                    <input
                        type="file"
                        id="receipt-picker"
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="receipt-scan-file-input"
                    >
                    <input
                        type="file"
                        id="receipt-camera"
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        capture="environment"
                        class="receipt-scan-file-input"
                    >
                    <div class="receipt-scan-sources">
                        <div class="receipt-scan-upload">
                            <div
                                class="receipt-dropzone"
                                id="receipt-dropzone"
                                role="button"
                                tabindex="0"
                                aria-label="Drag and drop or choose an image from your device"
                            >
                                <div class="receipt-dropzone__icon" aria-hidden="true">
                                    <svg class="receipt-dropzone__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M24 8v20M14 18l10-10 10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 32h28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <p class="receipt-dropzone__title">Upload image</p>
                                <p class="receipt-dropzone__subtitle">Drag & drop or choose a file</p>
                                <span class="receipt-dropzone__btn">Choose file</span>
                            </div>
                        </div>

                        <div
                            class="receipt-camera-box"
                            id="receipt-camera-box"
                            role="button"
                            tabindex="0"
                            aria-label="Open camera to take a photo of the receipt"
                        >
                            <div class="receipt-camera-box__icon" aria-hidden="true">
                                <svg class="receipt-camera-box__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 14h6l2-3h16l2 3h6a2 2 0 0 1 2 2v19a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V16a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                    <circle cx="24" cy="27" r="7" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <p class="receipt-camera-box__title">Open camera</p>
                            <p class="receipt-camera-box__subtitle">Take a photo of your receipt</p>
                            <span class="receipt-camera-box__btn">Use camera</span>
                        </div>
                    </div>

                    <p class="receipt-scan-formats">
                        <strong>Supported formats:</strong> JPG, JPEG, PNG, WEBP
                        <span class="receipt-scan-formats__sep">·</span>
                        max 5 MB
                    </p>
                    <p class="receipt-dropzone__filename" id="receipt-filename" hidden></p>

                    @error('receipt') <div class="text-danger" style="margin-top:0.5rem;">{{ $message }}</div> @enderror

                    <div id="receipt-preview-wrap" style="display:none; margin-top:1rem;">
                        <p style="margin:0 0 0.5rem; font-size:0.85rem; color:var(--txt2); font-weight:600;">Preview</p>
                        <img id="receipt-preview" alt="" style="max-width:100%; max-height:280px; border-radius:12px; border:1px solid var(--border2);">
                    </div>
                    </div>

                    <div class="receipt-scan-card__footer">
                        <button type="submit" class="btn btn-primary">Scan receipt</button>
                        <button type="button" class="btn btn-secondary" id="receipt-cancel-btn">Cancel</button>
                    </div>

                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="receipt-scan-col">
        <div class="card receipt-scan-card">
            <div class="receipt-scan-card__inner">
            <div class="receipt-scan-card__header">
                <p class="receipt-scan-card__heading">Detected from receipt</p>
                @if($hasScan)
                <span class="receipt-scan-confidence">{{ $confidenceLabel }}</span>
                @endif
            </div>

            @if(!$hasScan)
            <div class="receipt-scan-placeholder" id="receipt-detected-placeholder">
                <div class="receipt-scan-placeholder__fields">
                <div class="form-group">
                    <label for="category_id_placeholder">Category</label>
                    <select id="category_id_placeholder" class="select-control" disabled>
                        <option value="">Select</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount_placeholder">Amount</label>
                    <div style="display:flex; align-items:stretch; gap:0.35rem;">
                        <div style="display:flex; align-items:center; justify-content:center; padding:0 0.65rem; background:var(--surface2); border-radius:8px; border:1px solid var(--border2); font-size:0.9rem; color:var(--txt);">
                            {{ $currencySymbol ?? '₺' }}
                        </div>
                        <input type="number" id="amount_placeholder" disabled placeholder="—">
                    </div>
                </div>
                <div class="form-group">
                    <label for="expense_date_placeholder">Expense date</label>
                    <input type="text" id="expense_date_placeholder" class="select-control" disabled placeholder="—" style="width:100%;">
                </div>
                <div class="form-group">
                    <label for="description_placeholder">Description / merchant</label>
                    <textarea id="description_placeholder" rows="2" disabled placeholder="Optional"></textarea>
                </div>
                </div>

                <div class="receipt-scan-card__footer">
                    <button type="button" class="btn btn-primary" disabled>Save expense</button>
                </div>
            </div>
            @else
            <form method="POST" action="{{ route('expenses.receipt-scan.confirm') }}" class="receipt-scan-form">
                @csrf
                <div class="receipt-scan-form__main">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="select-control select-enhanced" required>
                        <option value="">Select</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}" {{ (string) old('category_id', $scan['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                    @if(!empty($scan['category_source']) && ($scan['category_source'] ?? '') === 'memory')
                        <p style="margin:0.35rem 0 0; font-size:0.78rem; color:var(--muted);">
                            Category suggested from your saved receipt history.
                        </p>
                    @elseif(!empty($scan['category_name']) && empty($scan['category_id']))
                        <p style="margin:0.35rem 0 0; font-size:0.78rem; color:var(--muted);">
                            Detected “{{ $scan['category_name'] }}” but no matching category — please pick one (it will be remembered next time).
                        </p>
                    @endif
                    @error('category_id') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <div style="display:flex; align-items:stretch; gap:0.35rem;">
                        <div style="display:flex; align-items:center; justify-content:center; padding:0 0.65rem; background:var(--surface2); border-radius:8px; border:1px solid var(--border2); font-size:0.9rem; color:var(--txt);">
                            {{ $currencySymbol ?? '₺' }}
                        </div>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                               value="{{ old('amount', $scan['amount'] ?? '') }}" required>
                    </div>
                    @error('amount') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="expense_date">Expense date</label>
                    @include('partials.date-input', [
                        'id' => 'expense_date',
                        'name' => 'expense_date',
                        'value' => old('expense_date', $scan['expense_date'] ?? date('Y-m-d')),
                        'max' => date('Y-m-d'),
                        'required' => true,
                    ])
                    @error('expense_date') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="description">Description / merchant</label>
                    <textarea id="description" name="description" rows="2" placeholder="Optional">{{ old('description', $scan['description'] ?? '') }}</textarea>
                    @if(!empty($scan['description']) && !empty($scan['description_source']))
                        <p style="margin:0.35rem 0 0; font-size:0.78rem; color:var(--muted);">
                            Suggested from receipt ({{ $scan['description_source'] === 'ai' ? 'AI' : 'OCR' }}). You can edit before saving.
                        </p>
                    @endif
                    @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                </div>

                <div class="receipt-scan-card__footer">
                    <button type="submit" class="btn btn-primary">Save expense</button>
                </div>
            </form>

            @if(!empty($scan['raw_text']))
            <details style="margin-top:1rem; padding:0.75rem 0 0; border-top:1px solid var(--border2);">
                <summary style="cursor:pointer; font-weight:600; color:var(--txt2);">OCR raw text</summary>
                <pre style="margin:0.75rem 0 0; white-space:pre-wrap; font-size:0.78rem; color:var(--muted); max-height:200px; overflow:auto;">{{ $scan['raw_text'] }}</pre>
            </details>
            @endif
            @endif
            </div>
        </div>
    </div>
</div>

<div id="receipt-camera-modal" class="receipt-camera-modal" hidden>
    <div class="receipt-camera-modal__backdrop" data-camera-close aria-hidden="true"></div>
    <div class="receipt-camera-modal__panel" role="dialog" aria-modal="true" aria-labelledby="receipt-camera-modal-title">
        <button type="button" class="receipt-camera-modal__close" data-camera-close aria-label="Close camera">&times;</button>
        <p id="receipt-camera-modal-title" class="receipt-camera-modal__title">Take a photo of your receipt</p>
        <div class="receipt-camera-modal__video-wrap" id="receipt-camera-video-wrap">
            <div class="receipt-camera-modal__loading" id="receipt-camera-loading" aria-live="polite">
                <span class="receipt-camera-modal__spinner" aria-hidden="true"></span>
                <span>Starting camera…</span>
            </div>
            <video id="receipt-camera-video" class="receipt-camera-modal__video" playsinline muted></video>
            <p id="receipt-camera-error" class="receipt-camera-modal__error" role="alert" hidden></p>
        </div>
        <p class="receipt-camera-modal__hint">Hold the receipt flat in the frame, then capture.</p>
        <div class="receipt-camera-modal__actions">
            <button type="button" class="btn btn-primary receipt-camera-modal__capture" id="receipt-camera-capture" disabled>Capture photo</button>
            <button type="button" class="btn btn-secondary" data-camera-close>Cancel</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .receipt-scan-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: stretch;
    }
    .receipt-scan-col {
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    .receipt-scan-card {
        margin-bottom: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .receipt-scan-card__inner {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .receipt-scan-card__label {
        display: block;
        margin: 0 0 0.5rem;
        font-weight: 600;
        color: var(--txt);
    }
    .receipt-scan-card__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        flex-shrink: 0;
    }
    .receipt-scan-card__heading {
        margin: 0;
        font-weight: 600;
        color: var(--txt);
    }
    .receipt-scan-confidence {
        font-size: 0.8rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: var(--surface2);
        color: var(--txt2);
        white-space: nowrap;
    }
    .receipt-scan-form {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .receipt-scan-form__main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .receipt-scan-sources {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
        min-height: 0;
    }
    .receipt-scan-upload {
        display: block;
    }
    .receipt-scan-placeholder {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .receipt-scan-placeholder__fields {
        flex: 1;
    }
    .receipt-scan-card__footer {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 1rem;
        flex-shrink: 0;
    }
    .receipt-scan-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .receipt-dropzone,
    .receipt-camera-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
        min-height: 8.5rem;
        padding: 1.25rem 1rem;
        border-radius: 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        user-select: none;
    }
    .receipt-dropzone {
        border: 2px dashed rgba(37, 99, 235, 0.28);
        background: linear-gradient(165deg, rgba(37, 99, 235, 0.07) 0%, var(--surface2) 48%, var(--surface) 100%);
    }
    .receipt-camera-box {
        border: 2px dashed rgba(13, 148, 136, 0.35);
        background: linear-gradient(165deg, rgba(13, 148, 136, 0.08) 0%, var(--surface2) 48%, var(--surface) 100%);
    }
    .receipt-camera-box:hover,
    .receipt-camera-box:focus-visible {
        border-color: #0d9488;
        background: linear-gradient(165deg, rgba(13, 148, 136, 0.14) 0%, rgba(13, 148, 136, 0.06) 55%, var(--surface) 100%);
        box-shadow: 0 8px 24px rgba(13, 148, 136, 0.18);
        outline: none;
    }
    .receipt-camera-box.is-has-file {
        border-style: solid;
        border-color: rgba(22, 163, 74, 0.45);
        background: linear-gradient(165deg, rgba(22, 163, 74, 0.08) 0%, var(--surface2) 100%);
    }
    .receipt-dropzone:hover,
    .receipt-dropzone:focus-visible {
        border-color: var(--acc);
        background: linear-gradient(165deg, rgba(37, 99, 235, 0.12) 0%, var(--acc-light) 55%, var(--surface) 100%);
        box-shadow: 0 8px 28px var(--acc-glow);
        outline: none;
    }
    .receipt-dropzone.is-dragover {
        border-color: var(--acc);
        border-style: solid;
        background: var(--acc-light);
        box-shadow: 0 0 0 4px var(--acc-glow);
        transform: scale(1.01);
    }
    .receipt-dropzone.is-has-file {
        border-style: solid;
        border-color: rgba(22, 163, 74, 0.45);
        background: linear-gradient(165deg, rgba(22, 163, 74, 0.08) 0%, var(--surface2) 100%);
    }
    .receipt-dropzone.is-has-file .receipt-dropzone__icon,
    .receipt-camera-box.is-has-file .receipt-camera-box__icon {
        background: rgba(22, 163, 74, 0.12);
        color: #16a34a;
    }
    .receipt-dropzone__icon,
    .receipt-camera-box__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.5rem;
        height: 3.5rem;
        margin-bottom: 0.35rem;
        border-radius: 50%;
        background: var(--acc-light);
        color: var(--acc);
        transition: background 0.2s ease, color 0.2s ease;
    }
    .receipt-dropzone__svg,
    .receipt-camera-box__svg {
        width: 2.35rem;
        height: 2.35rem;
    }
    .receipt-camera-box__icon {
        background: rgba(13, 148, 136, 0.12);
        color: #0d9488;
    }
    .receipt-dropzone__title,
    .receipt-camera-box__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--txt);
        line-height: 1.35;
    }
    .receipt-dropzone__subtitle,
    .receipt-camera-box__subtitle {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--txt2);
        line-height: 1.4;
    }
    .receipt-dropzone__btn,
    .receipt-camera-box__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 0.5rem;
        padding: 0.5rem 1.15rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, var(--acc2));
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        pointer-events: none;
    }
    .receipt-dropzone__filename {
        margin: 0.5rem 0 0;
        max-width: 100%;
        font-size: 0.82rem;
        font-weight: 600;
        color: #16a34a;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: center;
    }
    .receipt-scan-formats {
        margin: 0.65rem 0 0;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--muted);
        text-align: center;
        line-height: 1.45;
    }
    .receipt-scan-formats strong {
        color: var(--txt2);
        font-weight: 600;
    }
    .receipt-scan-formats__sep {
        margin: 0 0.2rem;
    }
    .receipt-camera-box__btn {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);
    }
    .receipt-camera-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        box-sizing: border-box;
    }
    .receipt-camera-modal[hidden] {
        display: none !important;
    }
    .receipt-camera-modal.is-open {
        animation: receipt-camera-fade-in 0.2s ease;
    }
    @keyframes receipt-camera-fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .receipt-camera-modal__backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .receipt-camera-modal__panel {
        position: relative;
        z-index: 1;
        width: min(36rem, calc(100vw - 2.5rem));
        max-height: calc(100vh - 2.5rem);
        overflow: auto;
        padding: 1.35rem;
        border-radius: 16px;
        background: var(--surface);
        border: 1px solid var(--border2);
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.35);
        animation: receipt-camera-panel-in 0.22s ease;
    }
    @keyframes receipt-camera-panel-in {
        from {
            opacity: 0;
            transform: translateY(12px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .receipt-camera-modal__close {
        position: absolute;
        top: 0.65rem;
        right: 0.65rem;
        width: 2rem;
        height: 2rem;
        padding: 0;
        border: none;
        border-radius: 8px;
        background: var(--surface2);
        color: var(--txt2);
        font-size: 1.35rem;
        line-height: 1;
        cursor: pointer;
    }
    .receipt-camera-modal__close:hover {
        background: var(--border2);
        color: var(--txt);
    }
    .receipt-camera-modal__title {
        margin: 0 0 0.75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--txt);
    }
    .receipt-camera-modal__video-wrap {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background: #020617;
        min-height: 16rem;
        aspect-ratio: 4 / 3;
    }
    .receipt-camera-modal__video {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 16rem;
        object-fit: contain;
        background: #020617;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .receipt-camera-modal__video-wrap.is-ready .receipt-camera-modal__video {
        opacity: 1;
    }
    .receipt-camera-modal__loading {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        margin: 0;
        font-size: 0.88rem;
        font-weight: 500;
        color: #cbd5e1;
        background: #0f172a;
    }
    .receipt-camera-modal__video-wrap.is-ready .receipt-camera-modal__loading,
    .receipt-camera-modal__video-wrap.has-error .receipt-camera-modal__loading {
        display: none;
    }
    .receipt-camera-modal__spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid rgba(148, 163, 184, 0.35);
        border-top-color: #2dd4bf;
        border-radius: 50%;
        animation: receipt-camera-spin 0.75s linear infinite;
    }
    @keyframes receipt-camera-spin {
        to { transform: rotate(360deg); }
    }
    .receipt-camera-modal__error {
        position: absolute;
        inset: 0;
        z-index: 3;
        display: none;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 1rem;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 500;
        color: #fecaca;
        background: rgba(15, 23, 42, 0.92);
    }
    .receipt-camera-modal__error:not([hidden]) {
        display: flex;
    }
    .receipt-camera-modal__hint {
        margin: 0.65rem 0 0;
        font-size: 0.82rem;
        color: var(--txt2);
    }
    .receipt-camera-modal__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .receipt-camera-modal__capture:not(:disabled) {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        border-color: transparent;
    }
    .receipt-scan-placeholder input:disabled,
    .receipt-scan-placeholder select:disabled,
    .receipt-scan-placeholder textarea:disabled {
        opacity: 0.72;
        cursor: not-allowed;
        background: var(--surface2);
    }
    .receipt-scan-placeholder .btn-primary:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    @media (max-width: 900px) {
        .receipt-scan-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const input = document.getElementById('receipt');
    const picker = document.getElementById('receipt-picker');
    const camera = document.getElementById('receipt-camera');
    const wrap = document.getElementById('receipt-preview-wrap');
    const img = document.getElementById('receipt-preview');
    const dropzone = document.getElementById('receipt-dropzone');
    const cameraBox = document.getElementById('receipt-camera-box');
    const filenameEl = document.getElementById('receipt-filename');
    const cameraModal = document.getElementById('receipt-camera-modal');
    const cameraVideoWrap = document.getElementById('receipt-camera-video-wrap');
    const cameraVideo = document.getElementById('receipt-camera-video');
    const cameraError = document.getElementById('receipt-camera-error');
    const cameraCaptureBtn = document.getElementById('receipt-camera-capture');

    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const ALLOWED_EXT = /\.(jpe?g|png|webp)$/i;
    const MAX_BYTES = 5 * 1024 * 1024;

    if (!input || !picker) return;

    if (cameraModal && cameraModal.parentElement !== document.body) {
        document.body.appendChild(cameraModal);
    }

    let cameraStream = null;

    const isAllowedImage = (file) => {
        if (!file) return false;
        if (ALLOWED_TYPES.includes(file.type)) return true;
        return ALLOWED_EXT.test(file.name || '');
    };

    const showPreview = (file) => {
        if (!wrap || !img) return;
        if (!file) {
            wrap.style.display = 'none';
            img.removeAttribute('src');
            return;
        }
        img.src = URL.createObjectURL(file);
        wrap.style.display = 'block';
    };

    const setSourceFile = (file) => {
        const has = Boolean(file);
        dropzone?.classList.toggle('is-has-file', has);
        cameraBox?.classList.toggle('is-has-file', has);
        if (filenameEl) {
            if (has && file) {
                filenameEl.hidden = false;
                filenameEl.textContent = file.name;
            } else {
                filenameEl.hidden = true;
                filenameEl.textContent = '';
            }
        }
    };

    const toast = (msg, type) => {
        if (typeof window.appToast === 'function') {
            window.appToast(msg, type);
        }
    };

    const assignFile = (file) => {
        if (!file) return;
        if (!isAllowedImage(file)) {
            toast('Only JPG, JPEG, PNG and WEBP images are supported.', 'error');
            return;
        }
        if (file.size > MAX_BYTES) {
            toast('Image is too large (max 5 MB).', 'error');
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showPreview(file);
        setSourceFile(file);
    };

    const stopCameraStream = () => {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }
        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }
    };

    const setCameraReady = (ready) => {
        cameraVideoWrap?.classList.toggle('is-ready', ready);
        if (cameraCaptureBtn) cameraCaptureBtn.disabled = !ready;
    };

    const showCameraError = (message) => {
        cameraVideoWrap?.classList.remove('is-ready');
        cameraVideoWrap?.classList.add('has-error');
        if (cameraError) {
            cameraError.textContent = message;
            cameraError.hidden = false;
        }
        setCameraReady(false);
    };

    const resetCameraUi = () => {
        cameraVideoWrap?.classList.remove('has-error');
        setCameraReady(false);
        if (cameraError) {
            cameraError.hidden = true;
            cameraError.textContent = '';
        }
    };

    const closeCameraModal = () => {
        stopCameraStream();
        resetCameraUi();
        if (cameraModal) {
            cameraModal.hidden = true;
            cameraModal.classList.remove('is-open');
        }
        document.body.classList.remove('receipt-camera-modal-open');
        document.body.style.overflow = '';
    };

    const requestCameraStream = async () => {
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const attempts = isMobile
            ? [{ facingMode: { ideal: 'environment' } }, { facingMode: 'user' }, true]
            : [true, { facingMode: 'user' }];

        let lastError = null;
        for (const video of attempts) {
            try {
                return await navigator.mediaDevices.getUserMedia({
                    video: video === true ? { width: { ideal: 1280 }, height: { ideal: 720 } } : { ...video, width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false,
                });
            } catch (err) {
                lastError = err;
            }
        }
        throw lastError || new Error('Camera unavailable');
    };

    const openCameraModal = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            camera?.click();
            return;
        }

        if (cameraModal) {
            cameraModal.hidden = false;
            cameraModal.classList.add('is-open');
        }
        resetCameraUi();
        document.body.classList.add('receipt-camera-modal-open');
        document.body.style.overflow = 'hidden';

        try {
            stopCameraStream();
            cameraStream = await requestCameraStream();
            if (!cameraVideo) return;

            cameraVideo.srcObject = cameraStream;
            cameraVideo.onloadedmetadata = () => {
                cameraVideo.play().catch(() => {});
            };
            await cameraVideo.play();
            setCameraReady(true);
        } catch (err) {
            stopCameraStream();
            if (camera && /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                closeCameraModal();
                camera.click();
                return;
            }
            showCameraError('Could not open camera. Allow access in your browser or use Upload image.');
            if (cameraCaptureBtn) cameraCaptureBtn.disabled = true;
        }
    };

    const captureFromCamera = () => {
        if (!cameraVideo || !cameraVideo.videoWidth) {
            toast('Camera is not ready yet.', 'error');
            return;
        }
        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth;
        canvas.height = cameraVideo.videoHeight;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.drawImage(cameraVideo, 0, 0);
        canvas.toBlob((blob) => {
            if (!blob) {
                toast('Could not capture photo.', 'error');
                return;
            }
            if (blob.size > MAX_BYTES) {
                toast('Photo is too large (max 5 MB). Try again closer to the receipt.', 'error');
                return;
            }
            const file = new File([blob], `receipt-${Date.now()}.jpg`, { type: 'image/jpeg' });
            assignFile(file);
            closeCameraModal();
        }, 'image/jpeg', 0.92);
    };

    const clearFile = () => {
        closeCameraModal();
        input.value = '';
        picker.value = '';
        if (camera) camera.value = '';
        showPreview(null);
        setSourceFile(null);
    };

    picker.addEventListener('change', () => {
        const file = picker.files && picker.files[0];
        if (file) assignFile(file);
    });

    camera?.addEventListener('change', () => {
        const file = camera.files && camera.files[0];
        if (file) assignFile(file);
    });

    const openPicker = () => picker.click();

    document.getElementById('receipt-cancel-btn')?.addEventListener('click', clearFile);

    dropzone?.addEventListener('click', openPicker);
    dropzone?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openPicker();
        }
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach((evt) => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach((evt) => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer?.files?.[0];
            if (file) assignFile(file);
        });
    }

    cameraBox?.addEventListener('click', () => openCameraModal());
    cameraBox?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openCameraModal();
        }
    });

    cameraCaptureBtn?.addEventListener('click', captureFromCamera);
    cameraModal?.querySelectorAll('[data-camera-close]').forEach((el) => {
        el.addEventListener('click', closeCameraModal);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && cameraModal && !cameraModal.hidden) {
            closeCameraModal();
        }
    });
})();
</script>
@endpush
