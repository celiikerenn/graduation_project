@extends('layouts.app')

@section('title', 'Receipt Scan')

@section('content')
<h1>Receipt Scan</h1>
<p style="margin-top:-0.35rem; margin-bottom:1rem; color:var(--muted); font-size:0.9rem;">
    Upload a receipt photo. OCR reads amount, date and category — you confirm before saving.
</p>

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
                            <p class="receipt-scan-action-box__subtitle">select</p>
                        </div>
                        <p class="receipt-scan-or receipt-scan-or--large">or</p>
                        <div
                            class="receipt-scan-action-box"
                            id="receipt-camera-box"
                            role="button"
                            tabindex="0"
                            aria-label="Open camera to capture a receipt"
                        >
                            <p class="receipt-scan-action-box__title">Open camera</p>
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
                    <textarea id="description" name="description" rows="2" placeholder="Optional">{{ old('description', '') }}</textarea>
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

<div id="receipt-camera-modal" class="receipt-camera-modal" hidden>
    <div class="receipt-camera-modal__panel">
        <p class="receipt-camera-modal__title">Capture receipt</p>
        <div class="receipt-camera-modal__video-wrap">
            <video id="receipt-camera-video" class="receipt-camera-modal__video" autoplay playsinline muted></video>
        </div>
        <div class="receipt-camera-modal__actions">
            <button type="button" class="btn btn-primary" id="receipt-camera-capture">Capture</button>
            <button type="button" class="btn btn-secondary" id="receipt-camera-cancel">Close</button>
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
        min-height: 5.5rem;
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
    .receipt-scan-action-box__subtitle {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--txt);
        line-height: 1.3;
    }
    body.receipt-camera-open .app-shell,
    body.receipt-camera-open .main {
        pointer-events: none;
    }
    body.receipt-camera-open .receipt-camera-modal {
        pointer-events: auto;
    }
    .receipt-camera-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.62);
        box-sizing: border-box;
    }
    .receipt-camera-modal[hidden] {
        display: none !important;
    }
    .receipt-camera-modal__panel {
        display: flex;
        flex-direction: column;
        gap: 1.35rem;
        width: min(94vw, 44rem);
        max-height: calc(100vh - 2.5rem);
        padding: 1.75rem 2rem 2rem;
        border-radius: 16px;
        border: 1px solid var(--border2);
        background: var(--surface);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 24px 56px rgba(15, 23, 42, 0.22);
        box-sizing: border-box;
        overflow: hidden;
        pointer-events: auto;
    }
    .receipt-camera-modal__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--txt);
        line-height: 1.3;
    }
    .receipt-camera-modal__video-wrap {
        width: 100%;
        flex: 1 1 auto;
        min-height: 18rem;
        aspect-ratio: 4 / 3;
        max-height: min(72vh, 34rem);
        border-radius: 12px;
        border: 1px solid var(--border2);
        background: #000;
        overflow: hidden;
    }
    .receipt-camera-modal__video {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .receipt-camera-modal__actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        justify-content: flex-end;
        padding-top: 0.25rem;
    }
    .receipt-scan-or--large {
        margin: 1rem 0;
        text-align: center;
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--txt);
        line-height: 1;
        letter-spacing: 0.02em;
        text-transform: lowercase;
    }
    .receipt-scan-upload-hint {
        margin: 0.85rem 0 0;
        font-size: 0.85rem;
        color: var(--muted);
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
    const cameraBox = document.getElementById('receipt-camera-box');
    const cameraModal = document.getElementById('receipt-camera-modal');
    const cameraVideo = document.getElementById('receipt-camera-video');
    const cameraCapture = document.getElementById('receipt-camera-capture');
    const cameraCancel = document.getElementById('receipt-camera-cancel');

    if (!input || !picker) return;

    let cameraStream = null;

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

    const stopCamera = () => {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }
        if (cameraVideo) cameraVideo.srcObject = null;
        if (cameraModal) cameraModal.hidden = true;
        document.body.classList.remove('receipt-camera-open');
    };

    const openCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            alert('Camera is not supported in this browser.');
            return;
        }

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            if (cameraVideo) {
                cameraVideo.srcObject = cameraStream;
            }
            if (cameraModal) cameraModal.hidden = false;
            document.body.classList.add('receipt-camera-open');
        } catch {
            alert('Could not access the camera. Check permissions and try again.');
            stopCamera();
        }
    };

    const captureFromCamera = () => {
        if (!cameraVideo || !cameraVideo.videoWidth) return;

        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth;
        canvas.height = cameraVideo.videoHeight;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) return;
            const file = new File([blob], `receipt-${Date.now()}.jpg`, { type: 'image/jpeg' });
            assignFile(file);
            stopCamera();
        }, 'image/jpeg', 0.92);
    };

    dropzone?.addEventListener('click', openPicker);
    dropzone?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openPicker();
        }
    });

    cameraBox?.addEventListener('click', openCamera);
    cameraBox?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openCamera();
        }
    });

    cameraCapture?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        captureFromCamera();
    });
    cameraCancel?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        stopCamera();
    });
    cameraModal?.querySelector('.receipt-camera-modal__panel')?.addEventListener('click', (e) => {
        e.stopPropagation();
    });
    cameraModal?.addEventListener('click', (e) => {
        if (e.target === cameraModal) stopCamera();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && cameraModal && !cameraModal.hidden) {
            e.preventDefault();
            stopCamera();
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
