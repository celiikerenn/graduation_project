"""
Harcama CRUD ve aylık özet API endpoint'leri.
Laravel, oturumdaki user_id ile istek atar.
"""
from datetime import date
from decimal import Decimal
import io
import logging
import os
import re

import pytesseract
from PIL import Image
from fastapi import APIRouter, Depends, HTTPException, Query, UploadFile, File, Form
from sqlalchemy.orm import Session, joinedload
from sqlalchemy import func, extract

from app.config import settings
from app.database import get_db
from app.models_db import Expense, ExpenseCategory, User
from app.schemas import (
    ExpenseCreate,
    ExpenseUpdate,
    ExpenseResponse,
    ExpenseListResponse,
    MonthlyTotalResponse,
)

router = APIRouter(prefix="/expenses", tags=["expenses"])
logger = logging.getLogger(__name__)


def _configure_tesseract_path() -> None:
    configured_cmd = settings.TESSERACT_CMD or os.getenv("TESSERACT_CMD")
    if configured_cmd and os.path.exists(configured_cmd):
        pytesseract.pytesseract.tesseract_cmd = configured_cmd
        return

    common_windows_path = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
    if os.path.exists(common_windows_path):
        pytesseract.pytesseract.tesseract_cmd = common_windows_path


_configure_tesseract_path()


def _normalize_category(raw: str) -> str:
    value = (raw or "").strip().lower()
    category_map = {
        "food": "Food",
        "transport": "Transport",
        "rent": "Rent",
        "utilities": "Utilities",
        "groceries": "Groceries",
        "health": "Health",
        "education": "Education",
        "entertainment": "Entertainment",
        "clothing": "Clothing",
        "other": "Other",
    }
    return category_map.get(value, "Other")


def _receipt_image_to_ocr_text(image: Image.Image) -> str:
    gray = image.convert("L")
    processed = gray.point(lambda x: 0 if x < 170 else 255, mode="1")
    try:
        return pytesseract.image_to_string(processed, lang="tur+eng", config="--psm 6")
    except pytesseract.TesseractNotFoundError as e:
        raise HTTPException(
            status_code=503,
            detail=(
                "Tesseract OCR bulunamadı. Kurup TESSERACT_CMD ayarla: "
                "https://github.com/UB-Mannheim/tesseract/wiki"
            ),
        ) from e
    except Exception:
        return pytesseract.image_to_string(processed, lang="eng", config="--psm 6")


def _parse_money_candidates(text: str) -> list[Decimal]:
    candidates: list[Decimal] = []
    money_pattern = r"(?<!\d)(?:\d{1,3}(?:[.,\s]\d{3})+|\d+)(?:[.,]\d{2})?(?!\d)"
    for raw in re.findall(money_pattern, text):
        token = raw.strip().replace(" ", "")
        if not token:
            continue
        try:
            if "." in token and "," in token:
                normalized = token.replace(".", "").replace(",", ".")
            elif "," in token:
                normalized = token.replace(".", "").replace(",", ".")
            else:
                normalized = token.replace(",", "")
            value = Decimal(normalized)
            if value > 0:
                candidates.append(value)
        except Exception:
            continue
    return candidates


def _extract_receipt_fields(text: str) -> tuple[str, Decimal, date, str]:
    lines = [ln.strip() for ln in text.replace("\r", "\n").split("\n") if ln.strip()]
    if not lines:
        return "Receipt expense", Decimal("0.0"), date.today(), "Other"

    store_name = "Receipt expense"
    for ln in lines[:8]:
        digit_ratio = sum(ch.isdigit() for ch in ln) / max(len(ln), 1)
        if digit_ratio < 0.45 and len(ln) > 2:
            store_name = ln[:120]
            break

    date_value = date.today()
    for m in re.finditer(r"\b(\d{1,2})[./-](\d{1,2})[./-](\d{2,4})\b", text):
        try:
            d, mo, y = int(m.group(1)), int(m.group(2)), int(m.group(3))
            if y < 100:
                y += 2000
            cand = date(y, mo, d)
            if date(2015, 1, 1) <= cand <= date.today():
                date_value = cand
                break
        except Exception:
            continue

    amount_value = Decimal("0.0")
    scored: list[tuple[int, Decimal]] = []
    prefer = ("TOPLAM", "TOTAL", "TUTAR", "ODENECEK", "ÖDENECEK", "NAKIT", "NAKİT", "KREDI", "KREDİ")
    avoid = ("KDV", "ARA TOPLAM", "ISKONTO", "İSKONTO", "INDIRIM", "İNDİRİM", "PARA ÜSTÜ", "PARA USTU")
    for ln in lines:
        vals = _parse_money_candidates(ln)
        if not vals:
            continue
        ln_upper = ln.upper()
        score = 0
        if any(k in ln_upper for k in prefer):
            score += 3
        if any(k in ln_upper for k in avoid):
            score -= 3
        for v in vals:
            if Decimal("0.01") <= v <= Decimal("1000000"):
                scored.append((score, v))
    if scored:
        scored.sort(key=lambda x: (x[0], x[1]))
        amount_value = scored[-1][1]

    text_upper = text.upper()
    category = "Other"
    if any(k in text_upper for k in ["OTOPARK", "TAXI", "TAKSİ", "AKARYAKIT", "METRO", "BUS", "ISPARK"]):
        category = "Transport"
    elif any(k in text_upper for k in ["MARKET", "MIGROS", "A101", "BIM", "BİM", "ŞOK", "SOK"]):
        category = "Groceries"
    elif any(k in text_upper for k in ["RESTORAN", "CAFE", "KAFE", "YEMEK", "FAST FOOD"]):
        category = "Food"
    elif any(k in text_upper for k in ["ELEKTRIK", "ELEKTRİK", "DOGALGAZ", "DOĞALGAZ", "INTERNET", "FATURA", "SU"]):
        category = "Utilities"
    elif any(k in text_upper for k in ["ECZANE", "HASTANE", "ILAC", "İLAÇ"]):
        category = "Health"
    elif any(k in text_upper for k in ["OKUL", "KURS", "KITAP", "KİTAP", "KIRTASIYE", "KIRTASİYE"]):
        category = "Education"
    elif any(k in text_upper for k in ["SINEMA", "KONSER", "EĞLENCE", "EGLENCE"]):
        category = "Entertainment"
    elif any(k in text_upper for k in ["GIYIM", "GİYİM", "AYAKKABI", "TEKSTIL", "TEKSTİL"]):
        category = "Clothing"
    elif any(k in text_upper for k in ["KIRA", "KİRA"]):
        category = "Rent"

    return store_name, amount_value, date_value, category


def _find_category_by_name(db: Session, category_name: str) -> ExpenseCategory | None:
    normalized = (category_name or "Other").strip().lower()
    all_categories = db.query(ExpenseCategory).all()
    for cat in all_categories:
        cat_name = (cat.name or "").strip().lower()
        if cat_name == normalized or normalized in cat_name or cat_name in normalized:
            return cat

    fallback = (
        db.query(ExpenseCategory)
        .filter(func.lower(ExpenseCategory.name).in_(["other", "diğer", "diger"]))
        .first()
    )
    if fallback is None:
        fallback = db.query(ExpenseCategory).order_by(ExpenseCategory.id.asc()).first()
    if fallback is None:
        fallback = ExpenseCategory(name="Other")
        db.add(fallback)
        db.commit()
        db.refresh(fallback)
    return fallback


def expense_to_response(exp: Expense) -> ExpenseResponse:
    """ORM Expense -> API response."""
    return ExpenseResponse(
        id=exp.id,
        user_id=exp.user_id,
        category_id=exp.category_id,
        category_name=exp.category.name if exp.category else "",
        amount=exp.amount,
        description=exp.description,
        expense_date=exp.expense_date,
        created_at=exp.created_at,
    )


@router.post("", response_model=ExpenseResponse)
def create_expense(data: ExpenseCreate, db: Session = Depends(get_db)):
    """
    Yeni harcama ekler.
    Laravel, giriş yapmış kullanıcının id'sini user_id olarak gönderir.
    """
    try:
        # Kategori var mı kontrol
        if data.expense_date > date.today():
            raise HTTPException(status_code=400, detail="Expense date cannot be in the future.")

        category = db.query(ExpenseCategory).filter(ExpenseCategory.id == data.category_id).first()
        if not category:
            raise HTTPException(status_code=400, detail="Invalid category id.")

        user = db.query(User).filter(User.id == data.user_id).first()
        if not user:
            raise HTTPException(status_code=400, detail="Invalid user id. Please login/register again.")

        expense = Expense(
            user_id=data.user_id,
            category_id=data.category_id,
            amount=data.amount,
            description=data.description,
            expense_date=data.expense_date,
        )
        # Response için category ilişkisini hazır tut
        expense.category = category
        db.add(expense)
        db.commit()
        db.refresh(expense)
        return expense_to_response(expense)
    except HTTPException:
        raise
    except Exception as e:
        db.rollback()
        err_msg = str(e)
        if hasattr(e, "orig"):
            err_msg = str(e.orig)
        elif hasattr(e, "__cause__") and e.__cause__:
            err_msg = str(e.__cause__)
        raise HTTPException(status_code=503, detail=f"Veritabanı hatası: {err_msg}") from e


@router.post("/ocr-create", response_model=ExpenseResponse)
async def create_expense_from_receipt(
    user_id: int = Form(...),
    receipt: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    """
    Fiş görselini Tesseract OCR ile analiz ederek mağaza adı/tutar/tarih/kategori çıkarır
    ve harcama oluşturur (tamamen yerel, ücretsiz).
    """
    # Bazı istemciler (özellikle multipart attach) content_type'ı boş veya
    # image/* yerine application/octet-stream gönderebilir.
    # Bu yüzden MIME kontrolünü esnek tutup gerçek doğrulamayı PIL ile yapıyoruz.
    filename = (receipt.filename or "").lower()
    image_extensions = (".jpg", ".jpeg", ".png", ".webp", ".bmp", ".tif", ".tiff")
    looks_like_image = (
        (receipt.content_type or "").startswith("image/")
        or filename.endswith(image_extensions)
    )

    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=400, detail="Invalid user id. Please login/register again.")

    try:
        raw = await receipt.read()
        if not raw:
            raise HTTPException(status_code=400, detail="Empty file uploaded.")
        if not looks_like_image:
            raise HTTPException(
                status_code=400,
                detail="Only image files are supported (jpg, jpeg, png, webp, bmp, tif, tiff).",
            )
        image = Image.open(io.BytesIO(raw)).convert("RGB")
        # Çok büyük görsellerde görseli küçült.
        max_side = 2000
        w, h = image.size
        if max(w, h) > max_side:
            ratio = max_side / float(max(w, h))
            image = image.resize(
                (max(1, int(w * ratio)), max(1, int(h * ratio))),
                Image.Resampling.LANCZOS,
            )
        ocr_text = _receipt_image_to_ocr_text(image)
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(
            status_code=400,
            detail=f"Receipt image could not be processed (invalid/corrupt image): {str(e)}",
        ) from e

    store_name, amount_value, date_value, category_name = _extract_receipt_fields(ocr_text)
    selected_category = _find_category_by_name(db, category_name)
    if selected_category is None:
        selected_category = db.query(ExpenseCategory).order_by(ExpenseCategory.id.asc()).first()
    if selected_category is None:
        raise HTTPException(status_code=400, detail="No expense category is available.")

    expense = Expense(
        user_id=user_id,
        category_id=selected_category.id,
        amount=amount_value,
        description=store_name,
        expense_date=date_value,
    )
    expense.category = selected_category

    try:
        db.add(expense)
        db.commit()
        db.refresh(expense)
    except Exception as e:
        db.rollback()
        err_msg = str(e.orig) if hasattr(e, "orig") else str(e)
        raise HTTPException(status_code=503, detail=f"Veritabanı hatası: {err_msg}") from e

    return expense_to_response(expense)


@router.get("", response_model=ExpenseListResponse)
def list_expenses_by_user(
    user_id: int = Query(..., description="Laravel'den gelen kullanıcı id"),
    skip: int = Query(0, ge=0),
    limit: int = Query(50, ge=1, le=200),
    db: Session = Depends(get_db),
):
    """
    Kullanıcıya ait harcamaları listeler (tarih sırasına göre, en yeni önce).
    """
    query = (
        db.query(Expense)
        .options(joinedload(Expense.category))
        .filter(Expense.user_id == user_id)
        .order_by(Expense.expense_date.desc(), Expense.created_at.desc())
    )
    total = query.count()
    expenses = query.offset(skip).limit(limit).all()
    return ExpenseListResponse(
        expenses=[expense_to_response(e) for e in expenses],
        total=total,
    )

@router.get("/monthly-total", response_model=MonthlyTotalResponse)
def get_monthly_total(
    user_id: int = Query(..., description="Kullanıcı id"),
    year: int = Query(..., ge=2000, le=2100),
    month: int = Query(..., ge=1, le=12),
    db: Session = Depends(get_db),
):
    """
    Belirtilen ay için kullanıcının toplam harcamasını ve adetini döner.
    Not: Bu endpoint dinamik /{expense_id} rotasından ÖNCE tanımlanmalıdır.
    """
    result = (
        db.query(
            func.coalesce(func.sum(Expense.amount), 0).label("total_amount"),
            func.count(Expense.id).label("expense_count"),
        )
        .filter(Expense.user_id == user_id)
        .filter(extract("year", Expense.expense_date) == year)
        .filter(extract("month", Expense.expense_date) == month)
        .first()
    )
    total_amount = Decimal(str(result.total_amount)) if result else Decimal("0")
    count = result.expense_count if result else 0
    return MonthlyTotalResponse(
        user_id=user_id,
        year=year,
        month=month,
        total_amount=total_amount,
        expense_count=count,
    )

@router.get("/{expense_id}", response_model=ExpenseResponse)
def get_expense(
    expense_id: int,
    user_id: int = Query(..., description="Kullanıcı id (Laravel session)"),
    db: Session = Depends(get_db),
):
    """Tek bir harcamayı döner (kullanıcıya ait olmalı)."""
    exp = (
        db.query(Expense)
        .options(joinedload(Expense.category))
        .filter(Expense.id == expense_id, Expense.user_id == user_id)
        .first()
    )
    if not exp:
        raise HTTPException(status_code=404, detail="Expense not found.")
    return expense_to_response(exp)


@router.put("/{expense_id}", response_model=ExpenseResponse)
def update_expense(
    expense_id: int,
    data: ExpenseUpdate,
    user_id: int = Query(..., description="Kullanıcı id (Laravel session)"),
    db: Session = Depends(get_db),
):
    """Harcama günceller (kullanıcıya ait olmalı)."""
    exp = db.query(Expense).filter(Expense.id == expense_id, Expense.user_id == user_id).first()
    if not exp:
        raise HTTPException(status_code=404, detail="Expense not found.")

    if data.category_id is not None:
        category = db.query(ExpenseCategory).filter(ExpenseCategory.id == data.category_id).first()
        if not category:
            raise HTTPException(status_code=400, detail="Invalid category id.")
        exp.category_id = data.category_id
        exp.category = category

    if data.amount is not None:
        exp.amount = data.amount
    if data.description is not None:
        exp.description = data.description
    if data.expense_date is not None:
        if data.expense_date > date.today():
            raise HTTPException(status_code=400, detail="Expense date cannot be in the future.")
        exp.expense_date = data.expense_date

    db.commit()
    db.refresh(exp)
    # category lazy-load olmasın
    exp = (
        db.query(Expense)
        .options(joinedload(Expense.category))
        .filter(Expense.id == expense_id, Expense.user_id == user_id)
        .first()
    )
    return expense_to_response(exp)


@router.delete("/{expense_id}")
def delete_expense(
    expense_id: int,
    user_id: int = Query(..., description="Kullanıcı id (Laravel session)"),
    db: Session = Depends(get_db),
):
    """Harcama siler (kullanıcıya ait olmalı)."""
    exp = db.query(Expense).filter(Expense.id == expense_id, Expense.user_id == user_id).first()
    if not exp:
        raise HTTPException(status_code=404, detail="Expense not found.")
    db.delete(exp)
    db.commit()
    return {"status": "ok", "deleted_id": expense_id}
