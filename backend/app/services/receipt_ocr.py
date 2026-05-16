"""
Receipt image OCR and lightweight field extraction.
Requires Tesseract OCR installed on the host (see README).
"""
from __future__ import annotations

import re
from datetime import date
from decimal import Decimal, InvalidOperation
from io import BytesIO
from typing import Optional

from PIL import Image, ImageOps

try:
    import pytesseract

    TESSERACT_AVAILABLE = True
except ImportError:
    pytesseract = None  # type: ignore
    TESSERACT_AVAILABLE = False


class OcrNotAvailableError(RuntimeError):
    pass


def _ensure_tesseract() -> None:
    if not TESSERACT_AVAILABLE:
        raise OcrNotAvailableError(
            "pytesseract is not installed. Run: pip install pytesseract Pillow"
        )
    try:
        pytesseract.get_tesseract_version()
    except Exception as exc:  # pragma: no cover
        raise OcrNotAvailableError(
            "Tesseract OCR is not installed or not on PATH. "
            "Install from https://github.com/tesseract-ocr/tesseract "
            "and ensure `tesseract` is available in your terminal."
        ) from exc


def _image_from_bytes(data: bytes) -> Image.Image:
    img = Image.open(BytesIO(data))
    img = ImageOps.exif_transpose(img)
    return img.convert("RGB")


def extract_text_from_image(data: bytes) -> str:
    _ensure_tesseract()
    img = _image_from_bytes(data)
    # Turkish + English receipts
    text = pytesseract.image_to_string(img, lang="eng+tur", config="--psm 6")
    if not text.strip():
        text = pytesseract.image_to_string(img, config="--psm 6")
    return text.strip()


def _normalize_amount_token(raw: str) -> Optional[Decimal]:
    """Parse Turkish (1.234,56) and dotted-decimal (230.06) amount strings."""
    s = raw.strip()
    s = re.sub(r"\s*(?:TL|TRY|₺)\s*$", "", s, flags=re.IGNORECASE).strip()
    s = re.sub(r"\s+", "", s)
    if not s or not re.search(r"\d", s):
        return None

    if "," in s and "." in s:
        if s.rfind(",") > s.rfind("."):
            s = s.replace(".", "").replace(",", ".")
        else:
            s = s.replace(",", "")
    elif "," in s:
        if re.search(r",\d{2}$", s):
            s = s.replace(".", "").replace(",", ".")
        else:
            s = s.replace(",", "")
    elif "." in s:
        if re.search(r"\.\d{2}$", s) and s.count(".") == 1:
            pass
        elif s.count(".") > 1:
            s = s.replace(".", "")
        elif re.search(r"\.\d{3}$", s):
            s = s.replace(".", "")

    try:
        value = Decimal(s)
        return value if value > 0 else None
    except InvalidOperation:
        return None


_AMOUNT_TOKEN = r"(\d{1,3}(?:[.\s]\d{3})*(?:[.,]\d{2})|\d+[.,]\d{2})"
_TOTAL_LABEL = re.compile(
    r"(?:TOPLAM|GENEL\s*TOPLAM|TOTAL|AMOUNT|TUTAR|ÖDENECEK|ODENECEK|KDV\s*DAHIL|KDV\s*DAHİL)",
    re.IGNORECASE,
)


def _amounts_on_line(line: str) -> list[Decimal]:
    found: list[Decimal] = []
    for match in re.finditer(_AMOUNT_TOKEN, line):
        value = _normalize_amount_token(match.group(1))
        if value is not None:
            found.append(value)
    return found


def _parse_amount(lines: list[str]) -> Optional[Decimal]:
    labeled: list[Decimal] = []
    with_currency: list[Decimal] = []
    all_amounts: list[Decimal] = []

    for line in lines:
        amounts = _amounts_on_line(line)
        if not amounts:
            continue
        all_amounts.extend(amounts)
        if _TOTAL_LABEL.search(line):
            labeled.extend(amounts)
        if re.search(r"(?:TL|TRY|₺)\b", line, re.IGNORECASE):
            with_currency.extend(amounts)

    if labeled:
        return labeled[-1]
    if with_currency:
        return with_currency[-1]
    if all_amounts:
        reasonable = [a for a in all_amounts if a < Decimal("1000000")]
        pool = reasonable or all_amounts
        return max(pool)
    return None


_CATEGORY_KEYWORDS: dict[str, list[str]] = {
    "Transport": [
        "ispark", "otopark", "parking", "park", "shell", "opet", "bp ", "petrol",
        "benzin", "akaryakit", "metro", "otobus", "otobüs", "bus", "taxi", "uber",
        "dolmus", "dolmuş", "hgs", "ogs", "toll",
    ],
    "Food": [
        "restaurant", "restoran", "cafe", "café", "kahve", "starbucks", "mcdonald",
        "burger", "yemek", "pizza", "kebab", "kebap", "lokanta",
    ],
    "Groceries": [
        "migros", "bim", "a101", "şok", "sok market", "carrefour", "market",
        "grocery", "grocer",
    ],
    "Utilities": ["elektrik", "su fatur", "dogalgaz", "doğalgaz", "internet", "turkcell", "vodafone"],
    "Health": ["eczane", "pharmacy", "hastane", "hospital", "clinic", "klinik"],
    "Entertainment": ["sinema", "cinema", "netflix", "spotify", "tiyatro", "bilet"],
    "Clothing": ["zara", "h&m", "lcw", "giyim", "tekstil"],
    "Education": ["okul", "universite", "üniversite", "kurs", "kitap"],
    "Rent": ["kira", "rent"],
}


def _parse_date(lines: list[str]) -> Optional[date]:
    text = "\n".join(lines)
    patterns = [
        r"(\d{2})[./\-](\d{2})[./\-](\d{4})",
        r"(\d{4})[./\-](\d{2})[./\-](\d{2})",
    ]
    for pattern in patterns:
        match = re.search(pattern, text)
        if not match:
            continue
        g = match.groups()
        try:
            if len(g[0]) == 4:
                y, m, d = int(g[0]), int(g[1]), int(g[2])
            else:
                d, m, y = int(g[0]), int(g[1]), int(g[2])
            parsed = date(y, m, d)
            if parsed <= date.today():
                return parsed
        except ValueError:
            continue
    return None


def _parse_merchant(lines: list[str]) -> Optional[str]:
    skip = re.compile(
        r"(fatura|receipt|fiş|fis|tel|phone|vergi|v\.?d\.?|tckn|www\.|http|@)",
        re.IGNORECASE,
    )
    for line in lines[:8]:
        clean = line.strip()
        if len(clean) < 3 or len(clean) > 60:
            continue
        if skip.search(clean):
            continue
        if re.fullmatch(r"[\d\W]+", clean):
            continue
        return clean[:120]
    return None


def _guess_category(raw_text: str, merchant: Optional[str]) -> Optional[str]:
    haystack = raw_text.lower()
    if merchant:
        haystack = f"{haystack}\n{merchant.lower()}"

    best_name: Optional[str] = None
    best_hits = 0
    for name, keywords in _CATEGORY_KEYWORDS.items():
        hits = sum(1 for kw in keywords if kw in haystack)
        if hits > best_hits:
            best_hits = hits
            best_name = name
    return best_name if best_hits > 0 else None


def parse_receipt_fields(raw_text: str) -> dict:
    lines = [ln.strip() for ln in raw_text.splitlines() if ln.strip()]
    amount = _parse_amount(lines)
    expense_date = _parse_date(lines)
    merchant = _parse_merchant(lines)
    category_name = _guess_category(raw_text, merchant)

    return {
        "raw_text": raw_text,
        "amount": float(amount) if amount is not None else None,
        "expense_date": expense_date.isoformat() if expense_date else None,
        "description": None,
        "category_name": category_name,
        "confidence": _confidence(amount, expense_date, category_name),
    }


def _confidence(
    amount: Optional[Decimal],
    expense_date: Optional[date],
    category_name: Optional[str],
) -> str:
    score = sum(
        [
            amount is not None,
            expense_date is not None,
            category_name is not None,
        ]
    )
    if score >= 2:
        return "high"
    if score == 1:
        return "medium"
    return "low"


def scan_receipt_image(data: bytes) -> dict:
    raw_text = extract_text_from_image(data)
    if not raw_text:
        return {
            "raw_text": "",
            "amount": None,
            "expense_date": None,
            "description": None,
            "category_name": None,
            "confidence": "low",
            "message": "No text detected on the receipt. Try a clearer photo or add the expense manually.",
        }
    fields = parse_receipt_fields(raw_text)
    fields["message"] = "Review the detected values before saving."
    return fields
