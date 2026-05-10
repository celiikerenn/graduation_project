"""
Gemini Vision ile fiş görselinden alan çıkarma — yumuşak hata davranışı (boş JSON).
"""
import io
import json
import logging
import re
from typing import Any

from PIL import Image

from app.config import settings

logger = logging.getLogger(__name__)

CANONICAL_CATEGORIES = frozenset({
    "Food",
    "Transport",
    "Rent",
    "Utilities",
    "Groceries",
    "Health",
    "Education",
    "Entertainment",
    "Clothing",
    "Other",
})


def empty_receipt_response() -> dict[str, Any]:
    return {
        "merchant_name": "",
        "date": "",
        "total": 0.0,
        "category": "Other",
        "items": [],
    }


def _keyword_category_rules() -> list[tuple[frozenset[str], str]]:
    """(anahtar kelime kümesi, canonical kategori)."""
    return [
        (
            frozenset(
                [
                    "yemek",
                    "restoran",
                    "restaurant",
                    "kafe",
                    "cafe",
                    "fast food",
                    "fastfood",
                    "lokanta",
                    "café",
                ]
            ),
            "Food",
        ),
        (
            frozenset(
                [
                    "otobüs",
                    "otobus",
                    "bus",
                    "taksi",
                    "taxi",
                    "otopark",
                    "parking",
                    "akaryakıt",
                    "akaryakit",
                    "benzin",
                    "petrol",
                    "fuel",
                    "metro",
                    "tramvay",
                ]
            ),
            "Transport",
        ),
        (
            frozenset(["kira", "rent"]),
            "Rent",
        ),
        (
            frozenset(
                [
                    "elektrik",
                    "electric",
                    "su",
                    "water",
                    "doğalgaz",
                    "dogalgaz",
                    "gaz",
                    "natural gas",
                    "internet",
                    "utility",
                    "fatura",
                ]
            ),
            "Utilities",
        ),
        (
            frozenset(
                [
                    "market",
                    "süpermarket",
                    "supermarket",
                    "grocer",
                    "grocery",
                    "migros",
                    "a101",
                    "bim",
                    "şok",
                    "sok",
                    "carrefour",
                ]
            ),
            "Groceries",
        ),
        (
            frozenset(
                [
                    "ilaç",
                    "ilac",
                    "eczane",
                    "pharmacy",
                    "hastane",
                    "hospital",
                    "clinic",
                    "sağlık",
                    "saglik",
                    "health",
                ]
            ),
            "Health",
        ),
        (
            frozenset(
                [
                    "okul",
                    "school",
                    "kurs",
                    "course",
                    "kitap",
                    "book",
                    "kırtasiye",
                    "kirtasiye",
                    "stationery",
                    "education",
                ]
            ),
            "Education",
        ),
        (
            frozenset(
                [
                    "sinema",
                    "cinema",
                    "konser",
                    "concert",
                    "eğlence",
                    "eglence",
                    "entertainment",
                    "theatre",
                    "theater",
                ]
            ),
            "Entertainment",
        ),
        (
            frozenset(
                [
                    "giyim",
                    "clothing",
                    "apparel",
                    "ayakkabı",
                    "ayakkabi",
                    "shoe",
                    "tekstil",
                    "textile",
                    "mağaza",
                    "magaza",
                    "butik",
                ]
            ),
            "Clothing",
        ),
        (
            frozenset(["diğer", "diger", "belirsiz", "unknown", "misc", "miscellaneous"]),
            "Other",
        ),
    ]


def map_category_from_hints(*hints: str) -> str:
    """Metin içinde anahtar kelime eşlemesi ile canonical kategori döndür."""
    blob = " ".join((h or "") for h in hints).strip().lower()
    if not blob:
        return "Other"
    for keywords, cat in _keyword_category_rules():
        if any(k in blob for k in keywords):
            return cat
    return "Other"


def _sanitize_category(raw: str, *hints: str) -> str:
    s = (raw or "").strip()
    if s in CANONICAL_CATEGORIES:
        return s
    mapped = map_category_from_hints(s, *hints)
    if mapped != "Other":
        return mapped
    return "Other"


def _parse_json_from_model_text(text: str) -> dict[str, Any] | None:
    if not text:
        return None
    t = text.strip()
    t = re.sub(r"^```(?:json)?\s*", "", t)
    t = re.sub(r"\s*```$", "", t)
    m = re.search(r"\{[\s\S]*\}", t)
    if not m:
        return None
    try:
        return json.loads(m.group())
    except json.JSONDecodeError:
        return None


def _coerce_items(raw_items: Any) -> list[dict[str, Any]]:
    if not isinstance(raw_items, list):
        return []
    out: list[dict[str, Any]] = []
    for row in raw_items:
        if not isinstance(row, dict):
            continue
        name = str(row.get("name", "") or "").strip()
        price_v = row.get("price", 0)
        try:
            price = float(price_v)
        except (TypeError, ValueError):
            price = 0.0
        if name:
            out.append({"name": name, "price": round(price, 2)})
    return out


def _coerce_float(v: Any) -> float:
    try:
        if v is None or v == "":
            return 0.0
        return round(float(v), 2)
    except (TypeError, ValueError):
        return 0.0


def _coerce_date_str(v: Any) -> str:
    if v is None:
        return ""
    s = str(v).strip()
    if not s:
        return ""
    # YYYY-MM-DD
    if re.match(r"^\d{4}-\d{2}-\d{2}$", s):
        return s
    return ""


def normalize_gemini_payload(data: dict[str, Any] | None, merchant_fallback: str) -> dict[str, Any]:
    if not isinstance(data, dict):
        base = {}
    else:
        base = data

    merchant = str(base.get("merchant_name") or "").strip()
    date_s = _coerce_date_str(base.get("date"))
    total = _coerce_float(base.get("total"))
    raw_cat = str(base.get("category") or "").strip()
    items = _coerce_items(base.get("items"))

    merchant_for_map = merchant or merchant_fallback
    category = _sanitize_category(raw_cat, merchant_for_map)

    return {
        "merchant_name": merchant,
        "date": date_s,
        "total": total,
        "category": category,
        "items": items,
    }


def analyze_receipt_image(image_bytes: bytes, mime_hint: str) -> dict[str, Any]:
    """Gemini ile fiş görselini analiz eder; tüm beklenmedik durumlarda boş güvenli cevap."""
    fallback = empty_receipt_response()

    if not image_bytes:
        return fallback.copy()

    if not (settings.GEMINI_API_KEY or "").strip():
        logger.warning("GEMINI_API_KEY boş — fiş analizi atlandı.")
        return fallback.copy()

    try:
        img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    except Exception as e:
        logger.warning("Görsel decode edilemedi: %s", e)
        return fallback.copy()

    prompt = """You are a receipt extraction assistant. Look at the receipt image and output ONLY a single JSON object (no markdown, no code fences) with this exact structure and key names:
{
  "merchant_name": "string, store or vendor name or empty",
  "date": "YYYY-MM-DD if visible else empty string",
  "total": number (final total paid, 0 if unknown),
  "category": "one of: Food, Transport, Rent, Utilities, Groceries, Health, Education, Entertainment, Clothing, Other — best guess from merchant and line items",
  "items": [ { "name": "string", "price": number } ]
}
Use English category names. If the receipt is unreadable or not a receipt, set merchant_name to "", date to "", total to 0, category to "Other", items to []."""

    try:
        import google.generativeai as genai

        genai.configure(api_key=settings.GEMINI_API_KEY.strip())
        model = genai.GenerativeModel("gemini-2.0-flash")
        response = model.generate_content([prompt, img])
    except Exception as e:
        err = str(e)
        if "429" in err or "depleted" in err.lower() or "RESOURCE_EXHAUSTED" in err:
            logger.warning(
                "Gemini 429: kota / ön ödemeli kredi tükendi — https://ai.google.dev/gemini-api/docs/billing"
            )
            logger.debug("Gemini tam hata: %s", e)
        else:
            logger.warning("Gemini isteği başarısız: %s", e)
        return fallback.copy()

    try:
        text = (response.text or "").strip()
    except Exception as e:
        logger.warning("Gemini yanıt metni okunamadı: %s", e)
        return fallback.copy()

    parsed = _parse_json_from_model_text(text)
    if not parsed:
        logger.warning("Gemini JSON parse edilemedi.")
        return fallback.copy()

    return normalize_gemini_payload(parsed, merchant_fallback="")
