"""
Receipt OCR scan API.
"""
from fastapi import APIRouter, File, Form, HTTPException, UploadFile

from app.schemas import ReceiptScanResponse

router = APIRouter(prefix="/receipts", tags=["receipts"])

ALLOWED_TYPES = {"image/jpeg", "image/png", "image/webp", "image/jpg"}
MAX_BYTES = 5 * 1024 * 1024


@router.post("/scan", response_model=ReceiptScanResponse)
async def scan_receipt(
    user_id: int = Form(...),
    file: UploadFile = File(...),
):
    if user_id < 1:
        raise HTTPException(status_code=400, detail="Invalid user id.")

    content_type = (file.content_type or "").lower()
    if content_type and content_type not in ALLOWED_TYPES:
        raise HTTPException(status_code=400, detail="Unsupported image type. Use JPG, PNG or WEBP.")

    data = await file.read()
    if not data:
        raise HTTPException(status_code=400, detail="Empty file.")
    if len(data) > MAX_BYTES:
        raise HTTPException(status_code=400, detail="File too large (max 5 MB).")

    try:
        from app.services.receipt_ocr import OcrNotAvailableError, scan_receipt_image

        result = scan_receipt_image(data)
    except OcrNotAvailableError as exc:
        raise HTTPException(status_code=503, detail=str(exc)) from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"OCR failed: {exc}") from exc

    return ReceiptScanResponse(
        user_id=user_id,
        raw_text=result.get("raw_text") or "",
        amount=result.get("amount"),
        expense_date=result.get("expense_date"),
        description=result.get("description"),
        category_name=result.get("category_name"),
        confidence=result.get("confidence") or "low",
        message=result.get("message") or "Review the detected values before saving.",
    )
