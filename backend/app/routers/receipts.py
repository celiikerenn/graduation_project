"""
Receipt OCR scan API.
"""
from fastapi import APIRouter, File, Form, HTTPException, UploadFile

from app.database import SessionLocal
from app.schemas import ReceiptLearnResponse, ReceiptScanResponse

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

    db = SessionLocal()
    try:
        from app.services.receipt_ocr import OcrNotAvailableError, scan_receipt_image

        result = scan_receipt_image(data, user_id=user_id, db=db)
    except OcrNotAvailableError as exc:
        raise HTTPException(status_code=503, detail=str(exc)) from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"OCR failed: {exc}") from exc
    finally:
        db.close()

    return ReceiptScanResponse(
        user_id=user_id,
        raw_text=result.get("raw_text") or "",
        amount=result.get("amount"),
        expense_date=result.get("expense_date"),
        description=result.get("description"),
        category_name=result.get("category_name"),
        category_source=result.get("category_source"),
        description_source=result.get("description_source"),
        confidence=result.get("confidence") or "low",
        message=result.get("message") or "Review the detected values before saving.",
    )


@router.post("/learn", response_model=ReceiptLearnResponse)
def learn_receipt_category(
    user_id: int = Form(...),
    category_id: int = Form(...),
    description: str = Form(""),
    raw_text: str = Form(""),
):
    """Remember merchant keywords from a confirmed receipt scan."""
    if user_id < 1:
        raise HTTPException(status_code=400, detail="Invalid user id.")
    if category_id < 1:
        raise HTTPException(status_code=400, detail="Invalid category id.")

    db = SessionLocal()
    try:
        from app.services.receipt_merchant_memory import learn_from_receipt

        saved = learn_from_receipt(
            db,
            user_id=user_id,
            category_id=category_id,
            description=description or None,
            raw_text=raw_text or "",
        )
    except Exception as exc:
        db.rollback()
        raise HTTPException(status_code=500, detail=f"Could not save receipt memory: {exc}") from exc
    finally:
        db.close()

    return ReceiptLearnResponse(user_id=user_id, keywords_saved=saved)
