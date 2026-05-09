@extends('layouts.app')

@section('title', 'Add Expense')

@push('styles')
<style>
    .receipt-file-status {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
        color: var(--muted);
        min-height: 1.25rem;
    }
    .receipt-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.65rem;
    }
    .receipt-toolbar .btn { margin: 0; }
    .receipt-camera-panel {
        display: none;
        margin-top: 0.75rem;
        padding: 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--border2);
        background: var(--surface2);
    }
    .receipt-camera-panel.is-open { display: block; }
    .receipt-camera-panel video {
        width: 100%;
        max-height: 280px;
        border-radius: 10px;
        background: #0f172a;
        object-fit: cover;
    }
    .receipt-camera-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.65rem;
    }
</style>
@endpush

@section('content')
<h1>Add Expense</h1>
<p style="margin-top:-0.35rem; margin-bottom:1rem; color:var(--muted); font-size:0.9rem;">
    Capture a new spending record with category, amount and date details.
</p>
<div class="card" style="margin-bottom:1rem;">
    <h2 style="margin-top:0; margin-bottom:0.75rem; font-size:1.1rem;">Auto-add from receipt (AI)</h2>
    <form method="POST" action="{{ route('expenses.ocr.store') }}" enctype="multipart/form-data" id="receipt-ocr-form">
        @csrf
        <div class="form-group">
            <label for="receipt">Receipt Photo</label>
            <input type="file" id="receipt" name="receipt" accept="image/*" required style="display:none;">
            <div class="receipt-toolbar">
                <button type="button" class="btn btn-secondary" id="receipt-pick-btn">Upload photo</button>
                <button type="button" class="btn btn-secondary" id="receipt-camera-btn">Use camera</button>
            </div>
            <p class="receipt-file-status" id="receipt-file-status" aria-live="polite">No photo selected</p>
            <div class="receipt-camera-panel" id="receipt-camera-panel" aria-hidden="true">
                <video id="receipt-video" playsinline muted autoplay></video>
                <div class="receipt-camera-actions">
                    <button type="button" class="btn btn-primary" id="receipt-capture-btn">Capture photo</button>
                    <button type="button" class="btn btn-secondary" id="receipt-camera-cancel">Close camera</button>
                </div>
                <p style="margin:0.5rem 0 0; font-size:0.8rem; color:var(--muted);">
                    Frame the receipt, then capture.
                </p>
            </div>
            <div style="font-size:0.82rem; color:var(--muted); margin-top:0.25rem;">
                Upload a photo or take one with the camera.
            </div>
            @error('receipt') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">
            Scan receipt &amp; add
        </button>
    </form>
</div>
<div class="card">
    <h2 style="margin-top:0; margin-bottom:0.75rem; font-size:1.1rem;">Manual expense</h2>
    <div style="display:flex; justify-content:space-between; align-items:center; gap:0.75rem; margin-bottom:0.9rem; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
            <button type="submit" form="fixed-expense-form" class="btn btn-secondary">Add this month's fixed expenses</button>
            <label style="display:flex; align-items:center; gap:0.45rem; cursor:default; margin:0;">
                <input type="checkbox" id="is_fixed_expense" value="1" disabled {{ !empty($autoFixedChecked) ? 'checked' : '' }}>
                <span style="font-size:0.86rem; color:var(--muted);">Added for this month</span>
            </label>
        </div>
        <form method="POST" action="{{ route('expenses.fixed-monthly.store') }}" style="margin:0;" id="fixed-expense-form">
            @csrf
        </form>
    </div>
    <div style="margin:-0.15rem 0 0.85rem; color:var(--muted); font-size:0.85rem;">
        Fixed templates are managed from <a href="{{ route('profile.show') }}" style="color:var(--acc); font-weight:600; text-decoration:underline;">Settings</a>.
    </div>
    <form method="POST" action="{{ route('expenses.store') }}">
        @csrf
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}" {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <div style="display:flex; align-items:stretch; gap:0.35rem;">
                <div style="display:flex; align-items:center; justify-content:center; padding:0 0.65rem; background:var(--surface2); border-radius:8px; border:1px solid var(--border2); font-size:0.9rem; color:var(--txt);">
                    {{ $currencySymbol }}
                </div>
                <input
                    type="number"
                    id="amount"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    value="{{ old('amount') }}"
                    required
                >
            </div>
            @error('amount') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="expense_date">Expense Date</label>
            <input type="date" id="expense_date" name="expense_date"
                   max="{{ date('Y-m-d') }}"
                   value="{{ old('expense_date', date('Y-m-d')) }}" required>
            @error('expense_date') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
            <button type="submit" class="btn btn-primary">
                Save Expense
            </button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('receipt');
    const fileStatus = document.getElementById('receipt-file-status');
    const pickBtn = document.getElementById('receipt-pick-btn');
    const camBtn = document.getElementById('receipt-camera-btn');
    const panel = document.getElementById('receipt-camera-panel');
    const video = document.getElementById('receipt-video');
    const captureBtn = document.getElementById('receipt-capture-btn');
    const cancelCam = document.getElementById('receipt-camera-cancel');

    let mediaStream = null;

    function setFileStatus() {
        const file = input && input.files && input.files[0];
        if (fileStatus) fileStatus.textContent = file ? file.name : 'No photo selected';
    }

    if (input) {
        input.addEventListener('change', setFileStatus);
    }

    if (pickBtn && input) {
        pickBtn.addEventListener('click', () => input.click());
    }

    function stopCamera() {
        if (mediaStream) {
            mediaStream.getTracks().forEach((t) => t.stop());
            mediaStream = null;
        }
        if (video) video.srcObject = null;
        if (panel) {
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
        }
    }

    async function openCamera() {
        if (!video || !panel) return;
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            window.alert('Tarayıcı kamera erişimini desteklemiyor. HTTPS veya localhost kullan veya “Upload photo” ile galeriden seç.');
            return;
        }
        stopCamera();
        try {
            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                audio: false,
            });
            video.srcObject = mediaStream;
            panel.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            await video.play().catch(() => {});
        } catch (err) {
            window.alert('Kamera açılamadı. İzin verildiğinden emin ol; mobilde genelde HTTPS gerekir.');
            stopCamera();
        }
    }

    if (camBtn) camBtn.addEventListener('click', openCamera);
    if (cancelCam) cancelCam.addEventListener('click', stopCamera);

    if (captureBtn && input && video) {
        captureBtn.addEventListener('click', () => {
            const w = video.videoWidth;
            const h = video.videoHeight;
            if (!w || !h) {
                window.alert('Kamera görüntüsü hazır değil; bir saniye bekleyip tekrar dene.');
                return;
            }
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;
            ctx.drawImage(video, 0, 0, w, h);
            canvas.toBlob(
                (blob) => {
                    if (!blob || !input) return;
                    const file = new File([blob], 'receipt-camera.jpg', { type: 'image/jpeg', lastModified: Date.now() });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    setFileStatus();
                    stopCamera();
                },
                'image/jpeg',
                0.92
            );
        });
    }

    const fixedCheckbox = document.getElementById('is_fixed_expense');
    const fixedForm = document.getElementById('fixed-expense-form');
    if (!fixedCheckbox) return;

    // Kullanıcı checkbox'ı elle değiştiremez; sadece fixed gider butonu işaretler.
    if (fixedForm) {
        fixedForm.addEventListener('submit', () => {
            fixedCheckbox.checked = true;
        });
    }
})();
</script>
@endpush

