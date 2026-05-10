@extends('layouts.app')

@section('title', 'Add Expense')

@section('content')
<h1>Add Expense</h1>
<p style="margin-top:-0.35rem; margin-bottom:1rem; color:var(--muted); font-size:0.9rem;">
    Add a spending record with category, amount and date.
</p>

<style>
    @keyframes receipt-ai-spin { to { transform: rotate(360deg); } }
    #receipt-ai-box .receipt-ai-spin {
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 50%;
        border: 2px solid var(--border2);
        border-top-color: var(--acc);
        animation: receipt-ai-spin 0.65s linear infinite;
    }
</style>
<div class="card" id="receipt-ai-box">
        <div style="font-weight:700; font-size:1rem; margin-bottom:0.65rem; color:var(--txt);">
            Auto-add from receipt (AI)
        </div>
        <p style="margin:0 0 0.65rem; font-size:0.86rem; color:var(--muted); line-height:1.35;">
            Upload or capture a receipt photo; the manual form below fills automatically when readable.
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
            <input type="file" id="receipt-ai-file-input" accept="image/*" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;" tabindex="-1" aria-hidden="true">
            <button type="button" class="btn btn-secondary" id="receipt-ai-upload-btn">Upload Photo</button>
            <button type="button" class="btn btn-secondary" id="receipt-ai-camera-open-btn">Open Camera</button>
            <button type="button" class="btn btn-secondary" id="receipt-ai-camera-close-btn" style="display:none;">Close camera</button>
        </div>
        <div id="receipt-ai-camera-area" style="display:none; margin-top:0.85rem;">
            <video id="receipt-ai-video" playsinline muted autoplay style="width:100%; max-width:360px; max-height:240px; border-radius:10px; background:#000;"></video>
            <div style="margin-top:0.5rem;">
                <button type="button" class="btn btn-primary" id="receipt-ai-shutter-btn">Take Photo</button>
            </div>
        </div>
        <div id="receipt-ai-preview-area" style="display:none; margin-top:0.85rem;">
            <img id="receipt-ai-preview-img" alt="Receipt preview" style="display:block; max-width:100%; max-height:260px; border-radius:10px; border:1px solid var(--border2);">
        </div>
        <div id="receipt-ai-loading" style="display:none; margin-top:0.85rem; align-items:center; gap:0.5rem; color:var(--txt2); font-size:0.9rem;">
            <span class="receipt-ai-spin" aria-hidden="true"></span>
            <span>Analyzing…</span>
        </div>
        <div id="receipt-ai-error" class="text-danger" style="display:none; margin-top:0.65rem;"></div>
</div>

<div class="card" id="manual-expense-box">
    <div style="font-weight:700; font-size:1rem; margin-bottom:0.85rem; color:var(--txt);">
        Add manually
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
        <hr style="margin:1.05rem 0 1rem; border:0; border-top:1px solid var(--border2);">
        <div style="margin-bottom:0.85rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:0.75rem; margin-bottom:0.65rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
                    <button type="submit" form="fixed-expense-form" class="btn btn-secondary">Add this month's fixed expenses</button>
                    <label style="display:flex; align-items:center; gap:0.45rem; cursor:default; margin:0;">
                        <input type="checkbox" id="is_fixed_expense" value="1" disabled {{ !empty($autoFixedChecked) ? 'checked' : '' }}>
                        <span style="font-size:0.86rem; color:var(--muted);">Added for this month</span>
                    </label>
                </div>
            </div>
            <div style="margin:0; color:var(--muted); font-size:0.85rem;">
                Fixed templates are managed from <a href="{{ route('profile.show') }}" style="color:var(--acc); font-weight:600; text-decoration:underline;">Settings</a>.
            </div>
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
    <form method="POST" action="{{ route('expenses.fixed-monthly.store') }}" style="margin:0;" id="fixed-expense-form">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const fixedCheckbox = document.getElementById('is_fixed_expense');
    const fixedForm = document.getElementById('fixed-expense-form');
    if (!fixedCheckbox) return;

    if (fixedForm) {
        fixedForm.addEventListener('submit', () => {
            fixedCheckbox.checked = true;
        });
    }
})();
(() => {
    const analyzeUrl = @json(route('expenses.receipt.analyze'));
    const loginUrl = @json(route('login'));
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const box = document.getElementById('receipt-ai-box');
    const fileInput = document.getElementById('receipt-ai-file-input');
    const btnUpload = document.getElementById('receipt-ai-upload-btn');
    const btnCamOpen = document.getElementById('receipt-ai-camera-open-btn');
    const btnCamClose = document.getElementById('receipt-ai-camera-close-btn');
    const camArea = document.getElementById('receipt-ai-camera-area');
    const video = document.getElementById('receipt-ai-video');
    const btnShutter = document.getElementById('receipt-ai-shutter-btn');
    const previewArea = document.getElementById('receipt-ai-preview-area');
    const previewImg = document.getElementById('receipt-ai-preview-img');
    const loadingRow = document.getElementById('receipt-ai-loading');
    const errEl = document.getElementById('receipt-ai-error');

    const amountEl = document.getElementById('amount');
    const dateEl = document.getElementById('expense_date');
    const descEl = document.getElementById('description');
    const catEl = document.getElementById('category_id');

    if (!box || !fileInput || !analyzeUrl || !csrf) return;

    let stream = null;
    let previewObjectUrl = null;

    function setPreviewFromBlob(blob) {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
        previewObjectUrl = URL.createObjectURL(blob);
        previewImg.src = previewObjectUrl;
        previewArea.style.display = 'block';
    }

    function hideError() {
        errEl.style.display = 'none';
        errEl.textContent = '';
    }

    function showError(message) {
        errEl.textContent = message || 'Could not read receipt, please fill manually';
        errEl.style.display = 'block';
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
        }
        if (video) video.srcObject = null;
        if (camArea) camArea.style.display = 'none';
        if (btnCamClose) btnCamClose.style.display = 'none';
    }

    function setLoading(on) {
        loadingRow.style.display = on ? 'flex' : 'none';
        if (on) errEl.style.display = 'none';
    }

    function receiptHasUsefulData(data) {
        if (!data || typeof data !== 'object') return false;
        const merchant = String(data.merchant_name || '').trim();
        const total = Number(data.total);
        const items = Array.isArray(data.items) ? data.items : [];
        if (merchant !== '') return true;
        if (total > 0 && !Number.isNaN(total)) return true;
        if (items.length > 0) return true;
        return false;
    }

    function pickCategory(value) {
        if (!catEl || !value) return;
        const want = String(value).trim().toLowerCase();
        let bestOpt = null;
        for (let i = 0; i < catEl.options.length; i++) {
            const opt = catEl.options[i];
            const txt = opt.textContent.trim().toLowerCase();
            if (txt === want) {
                catEl.selectedIndex = i;
                return;
            }
            if (!bestOpt && txt && want.includes(txt)) bestOpt = opt;
        }
        if (bestOpt) {
            bestOpt.selected = true;
            return;
        }
        for (let i = 0; i < catEl.options.length; i++) {
            const opt = catEl.options[i];
            if (opt.textContent.trim().toLowerCase() === 'other') {
                catEl.selectedIndex = i;
                return;
            }
        }
    }

    function clampDateToMax(isoDate) {
        if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return '';
        if (!dateEl) return isoDate;
        const maxD = dateEl.getAttribute('max');
        if (maxD && isoDate > maxD) return maxD;
        return isoDate;
    }

    function fillFormFromReceipt(data) {
        if (!data) return;
        const merchant = String(data.merchant_name || '').trim();
        const total = Number(data.total);
        const dateIso = clampDateToMax(String(data.date || '').trim());

        if (descEl && merchant !== '') descEl.value = merchant;
        if (amountEl && total > 0 && !Number.isNaN(total)) amountEl.value = String(total.toFixed(2));
        if (dateEl && dateIso) dateEl.value = dateIso;
        pickCategory(data.category || 'Other');
    }

    async function analyzeReceiptFile(file) {
        if (!file || !/^image\//i.test(file.type)) {
            showError('Could not read receipt, please fill manually');
            return;
        }
        hideError();
        setPreviewFromBlob(file);
        setLoading(true);

        const fd = new FormData();
        fd.append('receipt', file);

        try {
            const res = await fetch(analyzeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });

            let data = null;
            try {
                data = await res.json();
            } catch (e) {
                data = null;
            }

            if (res.status === 401) {
                window.location.href = loginUrl;
                return;
            }

            if (!res.ok) {
                showError('Could not read receipt, please fill manually');
                return;
            }

            if (!receiptHasUsefulData(data)) {
                showError('Could not read receipt, please fill manually');
                return;
            }

            fillFormFromReceipt(data);
        } catch (e) {
            showError('Could not read receipt, please fill manually');
        } finally {
            setLoading(false);
        }
    }

    btnUpload.addEventListener('click', () => {
        hideError();
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const f = fileInput.files && fileInput.files[0];
        fileInput.value = '';
        if (f) analyzeReceiptFile(f);
    });

    btnCamOpen.addEventListener('click', async () => {
        hideError();
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showError('Could not read receipt, please fill manually');
            return;
        }
        stopCamera();
        try {
            const constraints = {
                audio: false,
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
            };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (e1) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            } catch (e2) {
                showError('Could not read receipt, please fill manually');
                return;
            }
        }
        video.srcObject = stream;
        camArea.style.display = 'block';
        btnCamClose.style.display = 'inline-flex';
    });

    btnCamClose.addEventListener('click', () => stopCamera());

    btnShutter.addEventListener('click', () => {
        if (!stream || video.videoWidth < 2) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        stopCamera();
        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    showError('Could not read receipt, please fill manually');
                    return;
                }
                const file = new File([blob], 'receipt-camera.jpg', { type: 'image/jpeg' });
                analyzeReceiptFile(file);
            },
            'image/jpeg',
            0.92
        );
    });
})();
</script>
@endpush
