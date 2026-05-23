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
            <div class="form-group" style="margin-bottom:0;">
                <label for="receipt">Receipt image</label>

                @if($hasScan && !empty($receiptPreview['data']))
                <div id="receipt-preview-wrap" style="margin-top:0.35rem;">
                    <img
                        id="receipt-preview"
                        src="data:{{ $receiptPreview['mime'] ?? 'image/jpeg' }};base64,{{ $receiptPreview['data'] }}"
                        alt="Scanned receipt"
                        style="max-width:100%; max-height:320px; border-radius:12px; border:1px solid var(--border2); display:block;"
                    >
                </div>

                <form method="POST" action="{{ route('expenses.receipt-scan.discard') }}" style="margin-top:1rem;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Scan another receipt</button>
                </form>
                @else
                <form method="POST" action="{{ route('expenses.receipt-scan.store') }}" enctype="multipart/form-data" id="receipt-scan-form">
                    @csrf
                    <input
                        type="file"
                        id="receipt"
                        name="receipt"
                        accept="image/jpeg,image/png,image/webp"
                        required
                        class="receipt-scan-file-input"
                    >
                    <input
                        type="file"
                        id="receipt-picker"
                        accept="image/jpeg,image/png,image/webp"
                        class="receipt-scan-file-input"
                    >
                    <div class="receipt-scan-upload">
                        <div
                            class="receipt-scan-action-box"
                            id="receipt-dropzone"
                            role="button"
                            tabindex="0"
                            aria-label="Drag and drop or select a receipt image"
                        >
                            <p class="receipt-scan-action-box__title">Drag & drop</p>
                            <p class="receipt-scan-action-box__or">or</p>
                            <p class="receipt-scan-action-box__subtitle">select a file</p>
                        </div>
                        <p class="receipt-scan-upload-hint">JPG, PNG or WEBP · max 5 MB · needs Tesseract on the API server</p>
                    </div>
                    @error('receipt') <div class="text-danger" style="margin-top:0.5rem;">{{ $message }}</div> @enderror

                    <div id="receipt-preview-wrap" style="display:none; margin-top:1rem;">
                        <p style="margin:0 0 0.5rem; font-size:0.85rem; color:var(--txt2); font-weight:600;">Preview</p>
                        <img id="receipt-preview" alt="" style="max-width:100%; max-height:280px; border-radius:12px; border:1px solid var(--border2);">
                    </div>

                    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">Scan receipt</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="receipt-scan-col">
        <div class="card receipt-scan-card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem;">
                <p style="margin:0; font-weight:600; color:var(--txt);">Detected from receipt</p>
                @if($hasScan)
                <span style="font-size:0.8rem; padding:0.25rem 0.6rem; border-radius:999px; background:var(--surface2); color:var(--txt2);">{{ $confidenceLabel }}</span>
                @endif
            </div>

            @if(!$hasScan)
            <div class="receipt-scan-placeholder" id="receipt-detected-placeholder">
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

                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
                    <button type="button" class="btn btn-primary" disabled>Save expense</button>
                </div>
            </div>
            @else
            <form method="POST" action="{{ route('expenses.receipt-scan.confirm') }}">
                @csrf
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

                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
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

@endsection

@push('styles')
<style>
    .receipt-scan-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    .receipt-scan-col {
        min-width: 0;
    }
    .receipt-scan-card {
        margin-bottom: 0;
        height: 100%;
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
    .receipt-scan-upload {
        margin-top: 0.35rem;
    }
    .receipt-scan-action-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        width: 100%;
        min-height: 6.25rem;
        padding: 1.25rem 1rem;
        border: 2px dashed var(--border2);
        border-radius: 12px;
        background: var(--surface2);
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
        user-select: none;
    }
    .receipt-scan-action-box:hover,
    .receipt-scan-action-box:focus-visible {
        border-color: var(--acc);
        background: var(--acc-light);
        outline: none;
    }
    .receipt-scan-action-box.is-dragover {
        border-color: var(--acc);
        background: var(--acc-light);
    }
    .receipt-scan-action-box__title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--txt);
        line-height: 1.3;
    }
    .receipt-scan-action-box__or {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--txt);
        line-height: 1.2;
        text-transform: lowercase;
        letter-spacing: 0.02em;
    }
    .receipt-scan-action-box__subtitle {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--txt);
        line-height: 1.3;
    }
    .receipt-scan-upload-hint {
        margin: 0.85rem 0 0;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--txt2);
        text-align: center;
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
    const wrap = document.getElementById('receipt-preview-wrap');
    const img = document.getElementById('receipt-preview');
    const dropzone = document.getElementById('receipt-dropzone');

    if (!input || !picker) return;

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

    const assignFile = (file) => {
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showPreview(file);
    };

    picker.addEventListener('change', () => {
        const file = picker.files && picker.files[0];
        if (file) assignFile(file);
    });

    const openPicker = () => picker.click();

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
})();
</script>
@endpush
