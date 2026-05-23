"""
Turkish receipt category detection — unit-based (step 1) then keywords (step 2).
Returns English category names matching expense_categories (Food, Groceries, …).
"""
from __future__ import annotations

import re
from typing import Optional

# Priority for ties / fallback (highest first)
CATEGORY_PRIORITY: tuple[str, ...] = (
    "Transport",
    "Health",
    "Groceries",
    "Food",
    "Utilities",
    "Rent",
    "Education",
    "Entertainment",
    "Clothing",
    "Other",
)

# --- Step 1: units ---
_GROCERY_UNIT_RE = re.compile(
    r"(?:"
    r"(?:\d+[\s,.]?\d*)\s*(?:lt|ml|kg|gr|g|gram)\b|"
    r"\b(?:lt|ml|kg|gr|g)\s*\d|"
    r"\d+\s*(?:paket|kutu|sise|po[sş]et|rulo|top|torba)\b|"
    r"\d+\s*['\u2019]?\s*li\s+paket|"
    r"\bpaket\b"
    r")",
    re.IGNORECASE,
)

_FOOD_UNIT_RE = re.compile(
    r"\b(?:porsiyon|tabak|bardak|kase|dilim)\b",
    re.IGNORECASE,
)

_ADET_LINE_RE = re.compile(
    r"\d+\s*adet|adet\s*x|/\s*adet|tl\s*/\s*adet",
    re.IGNORECASE,
)

_PACKAGED_PRODUCT_RE = re.compile(
    r"\b(?:sut|ayran|simit|yogurt|peynir|kola|meyve\s*su)\b.*?"
    r"(?:\d+\s*(?:lt|ml|kg|gr|g)\b|(?:lt|ml)\s*\d+|\bpaket\b|\d+\s*['\u2019]?\s*li)",
    re.IGNORECASE,
)

_FOOD_PRODUCT_LINE_RE = re.compile(
    r"\b(?:tatli|yiyecek|icecek|kebap|kebapci|pide|lahmacun|pizza|doner|döner|"
    r"corba|pilav|makarna|kofte|iskender|borek|waffle|dondurma|"
    r"sutlac|kunefe|baklava|sandvic|tost|durum|petifur|petit|bardak|cay|kahve)\b",
    re.IGNORECASE,
)

_RESTAURANT_HEADER_RE = re.compile(
    r"(?:kebapci|kebap|restoran|lokanta|cafe|kafeterya|bistro|yiyecek|pastane|"
    r"firin|sut\s*mamul|tatli|doner|döner|pide|lahmacun)",
    re.IGNORECASE,
)

_RESTAURANT_ITEM_RE = re.compile(
    r"(?:bardak\s*cay|petifur|petit|yiyecek|icecek|tatli|corba|pilav|kofte|"
    r"iskender|tabak|porsiyon|menemen|omlet|waffle|baklava)",
    re.IGNORECASE,
)

_MENU_ADET_PRICE_RE = re.compile(
    r"(?:\d+\s*adet\s*x|adet\s*x\s*\d|tl\s*/\s*adet|/\s*adet)",
    re.IGNORECASE,
)

# --- Tier-1 overrides ---
_TRANSPORT_STRONG_RE = re.compile(
    r"\b(?:benzin|motorin|dizel|yakit|akaryakit|lpg|excellium|"
    r"hgs|ogs|otopark|park\s*ucreti|kopru\s*gecis|otoyol)\b|"
    r"\b(?:lt\s*x|l\s*x)\b",
    re.IGNORECASE,
)

_FUEL_STATION_RE = re.compile(
    r"\b(?:opet|shell|bp|total|aytemiz|petrol\s+ofisi|turkiye\s*petrolleri)\b",
    re.IGNORECASE,
)

_HEALTH_STRONG_RE = re.compile(
    r"\b(?:eczane|eczanesi|pharmacy)\b|"
    r"ecz\.?\s*[a-z]{2,}|"
    r"\b(?:ilac|recete|muayene|hastane|klinik|doktor|dis\s*hekim|tahlil)\b",
    re.IGNORECASE,
)

_CORPORATE_LINE_RE = re.compile(
    r"(?:ltd|şti|sti\b|san\.|tic\.|ve\s+tic|petrol\s+urun|urunleri\s+oto)",
    re.IGNORECASE,
)

_STORE_GROCERIES_RE = re.compile(
    r"\b(?:migros|bim|a101|sok|carrefour|hakmar|makro|kiler|file|"
    r"supermarket|market|manav|kasap)\b",
    re.IGNORECASE,
)

# --- Step 2: keyword lists (normalized Turkish) ---
_KEYWORDS: dict[str, tuple[str, ...]] = {
    "Food": (
        "restoran", "cafe", "kafeterya", "lokanta", "bistro", "buffet", "yemekhane",
        "cay", "kahve", "nescafe", "espresso", "americano", "latte", "cappuccino",
        "salep", "boza", "limonata", "corba", "mercimek corbasi", "tarhana", "pilav",
        "makarna", "lazanya", "manti", "kofte", "sis", "kebap", "doner", "iskender",
        "pide", "lahmacun", "pizza", "borek", "gozleme", "menemen", "sahanda", "omlet",
        "guvec", "tandir", "waffle", "kek", "baklava", "kunefe", "kadayif", "sutlac",
        "dondurma", "sandvic", "tost", "durum", "kokorec", "balik ekmek", "tabak",
        "porsiyon", "servis", "siparis", "yiyecek", "icecek", "tatli", "pastane",
        "firin", "sut mamul", "sut mamulleri", "unlu mamul", "lokanta",
    ),
    "Groceries": (
        "market", "supermarket", "manav", "kasap", "migros", "bim", "a101", "sok",
        "carrefour", "hakmar", "makro", "kiler", "file", "yogurt", "kefir", "peynir",
        "kasar", "beyaz peynir", "lor", "labne", "tereyagi", "krema", "yumurta",
        "tavuk but", "kiyma", "dana", "kuzu", "sucuk", "salam", "sosis", "pastirma",
        "ekmek", "lavas", "tortilla", "pirinç", "pirinc", "bulgur", "un", "irmik",
        "nohut", "mercimek", "fasulye", "elma", "armut", "muz", "portakal", "domates",
        "salatalik", "biber", "patates", "sogan", "sarimsak", "maden suyu", "kola",
        "fanta", "sprite", "deterjan", "camasir suyu", "bulasik", "temizlik", "sabun",
        "sampuan", "dis macunu", "cikolata tablet", "bisküvi", "biskivi", "gofret",
        "cips", "kraker",
    ),
    "Transport": (
        "taksi", "uber", "bolt", "otobus", "metro", "metrobüs", "metrobüs", "tramvay",
        "vapur", "feribot", "dolmus", "otogar", "terminal", "havalimani", "ucak",
        "benzin", "motorin", "yakit", "akaryakit", "lpg", "opet", "shell", "bp",
        "total", "petrol ofisi", "aytemiz", "otopark", "ispark", "hgs", "ogs",
        "gecis ucreti", "otoyol",
    ),
    "Utilities": (
        "elektrik", "dogalgaz", "su faturasi", "internet faturasi", "telefon faturasi",
        "iski", "igdas", "bedas", "enerjisa", "turkcell", "vodafone", "turk telekom",
        "ttnet", "superonline", "fiber", "fatura", "aidat", "abonelik", "sayac", "tuketim",
        "e-arsiv fatura", "earsiv",
    ),
    "Rent": (
        "kira", "kira bedeli", "kira odemesi", "konut kirasi", "site yonetimi",
        "yonetim odemesi", "apartman aidati", "konut bedeli",
    ),
    "Health": (
        "eczane", "eczanesi", "ilac", "vitamin", "takviye", "recete", "muayene",
        "hastane", "klinik", "doktor", "dis hekimi", "disçi", "optik", "gozluk", "lens",
        "serum", "bandaj", "pansuman", "rontgen", "ultrason", "tahlil", "laboratuvar",
        "fizik tedavi", "randevu", "fiyat farki",
    ),
    "Education": (
        "okul", "kurs", "dershane", "etut", "anaokulu", "kitap", "ders kitabi",
        "sozluk", "ansiklopedi", "kalem", "defter", "silgi", "cetvel", "kirtasiye",
        "dosya", "klasor", "sertifika", "seminer", "workshop", "egitim ucreti",
        "kayit ucreti",
    ),
    "Entertainment": (
        "sinema", "tiyatro", "konser", "muz", "müze", "sergi", "festival", "oyun",
        "eglence", "lunapark", "aquapark", "tema park", "bowling", "bilardo", "netflix",
        "spotify", "youtube premium", "exxen", "blutv", "gain", "kacis odasi", "gosteri",
    ),
    "Clothing": (
        "giyim", "kiyafet", "elbise", "pantolon", "gomlek", "kazak", "mont", "ceket",
        "ic camasir", "corap", "pijama", "mayo", "bikini", "ayakkabi", "bot", "sandalet",
        "terlik", "canta", "cuzdan", "kemer", "sapka", "eldiven", "esarp",
        "zara", "lcw", "koton", "defacto", "mango", "bershka", "nike", "adidas",
    ),
    "Other": (
        "diger", "cesitli", "muhtelif", "hizmet bedeli", "komisyon", "vergi", "kdv",
        "stopaj", "noter", "tapu", "sigorta", "danismanlik",
    ),
}

# Ambiguous alone → Food; with package unit → Groceries (checked in unit logic)
_AMBIGUOUS_FOOD_ALONE = frozenset({"sut", "ayran", "simit", "cay", "kahve"})


def normalize_text(text: str) -> str:
    t = (text or "").lower()
    t = t.replace("ı", "i").replace("İ", "i").replace("ş", "s").replace("ğ", "g")
    t = t.replace("ö", "o").replace("ü", "u").replace("ç", "c")
    t = re.sub(r"\s+", " ", t)
    return t.strip()


def _keyword_in_text(keyword: str, haystack: str) -> bool:
    kw = normalize_text(keyword)
    if len(kw) <= 3:
        return re.search(rf"(?<![a-z0-9]){re.escape(kw)}(?![a-z0-9])", haystack) is not None
    return kw in haystack


def _line_looks_like_product(line: str) -> bool:
    if re.search(r"(?:toplam|kdv|nakit|kredi|pos|fis\s*no|vergi|vkn|tarih|saat)", line, re.I):
        return False
    if re.search(r"\d+[.,]\d{2}", line):
        return True
    if _GROCERY_UNIT_RE.search(line) or _FOOD_UNIT_RE.search(line) or _ADET_LINE_RE.search(line):
        return True
    return bool(_FOOD_PRODUCT_LINE_RE.search(line))


def _classify_line_by_unit(line: str) -> Optional[str]:
    norm = normalize_text(line)
    if not norm:
        return None

    if _FOOD_UNIT_RE.search(norm):
        return "Food"

    if _FOOD_PRODUCT_LINE_RE.search(norm) and (
        _ADET_LINE_RE.search(norm) or re.search(r"/\s*adet", norm)
    ):
        return "Food"

    if _PACKAGED_PRODUCT_RE.search(norm) or _GROCERY_UNIT_RE.search(norm):
        return "Groceries"

    if _ADET_LINE_RE.search(norm):
        for word in _AMBIGUOUS_FOOD_ALONE:
            if re.search(rf"\b{word}\b", norm):
                if re.search(r"\d+\s*(?:lt|ml|kg|gr|g)\b|(?:lt|ml)\s*\d+|\bpaket\b", norm):
                    return "Groceries"
                return "Food"
        if (
            _FOOD_PRODUCT_LINE_RE.search(norm)
            or _RESTAURANT_ITEM_RE.search(norm)
            or (_MENU_ADET_PRICE_RE.search(norm) and re.search(r"\d+[.,]\d{2}", norm))
        ):
            return "Food"
        if re.search(r"\b(?:lt|ml|kg|gr|g|paket|kutu)\b", norm):
            return "Groceries"
        return None

    for word in _AMBIGUOUS_FOOD_ALONE:
        if re.search(rf"\b{word}\b", norm):
            if re.search(r"\d+\s*(?:lt|ml|kg|gr|g)\b|(?:lt|ml)\s*\d+|\bpaket\b", norm):
                return "Groceries"
            return "Food"

    return None


def _score_units(lines: list[str]) -> dict[str, int]:
    scores: dict[str, int] = {"Food": 0, "Groceries": 0}
    for line in lines:
        if not _line_looks_like_product(line):
            continue
        cat = _classify_line_by_unit(line)
        if cat:
            scores[cat] += 1
    return scores


def _has_corporate_petrol_only(text: str) -> bool:
    """Petrol/oto only in company legal name, not a fuel station receipt."""
    if not _CORPORATE_LINE_RE.search(text):
        return False
    if _FUEL_STATION_RE.search(text) or _TRANSPORT_STRONG_RE.search(text):
        return False
    if re.search(r"\bpetrol\b", text) and not re.search(r"\b(?:benzin|motorin|lt\s*x)\b", text):
        return True
    return False


def _tier1_transport(text: str, header: str) -> bool:
    if _has_corporate_petrol_only(header):
        return False
    if _FUEL_STATION_RE.search(text):
        return True
    if _TRANSPORT_STRONG_RE.search(text):
        if _FOOD_PRODUCT_LINE_RE.search(header) and not _FUEL_STATION_RE.search(text):
            return False
        return True
    return False


def _tier1_health(text: str) -> bool:
    return bool(_HEALTH_STRONG_RE.search(text))


def _is_restaurant_receipt(text: str, header: str, lines: list[str]) -> bool:
    """Restoran / kafe / kebapçı fişi — adet satırları market sayılmasın."""
    if _STORE_GROCERIES_RE.search(header):
        return False
    if _RESTAURANT_HEADER_RE.search(header):
        return True
    blob = normalize_text("\n".join(lines))
    if _RESTAURANT_ITEM_RE.search(blob) or re.search(r"\byiyecek\b", blob):
        return True
    menu_lines = sum(
        1
        for ln in lines
        if _MENU_ADET_PRICE_RE.search(ln) and re.search(r"\d+[.,]\d{2}", ln)
    )
    if menu_lines >= 2:
        return True
    return False


def _store_category(header: str, merchant: str) -> Optional[str]:
    blob = f"{header} {merchant}"
    if _STORE_GROCERIES_RE.search(blob):
        return "Groceries"
    if _FUEL_STATION_RE.search(blob) and not _has_corporate_petrol_only(blob):
        return "Transport"
    if _HEALTH_STRONG_RE.search(blob):
        return "Health"
    return None


def _score_keywords(text: str, lines: list[str], merchant: str) -> dict[str, int]:
    scores: dict[str, int] = {cat: 0 for cat in CATEGORY_PRIORITY}
    header = normalize_text("\n".join(lines[:6]))
    blob = f"{header} {normalize_text(merchant)} {text}"

    for category, keywords in _KEYWORDS.items():
        for kw in keywords:
            if _keyword_in_text(kw, blob):
                scores[category] += 2
        for line in lines:
            if not line.strip():
                continue
            ln = normalize_text(line)
            for kw in keywords:
                if _keyword_in_text(kw, ln):
                    scores[category] += 1

    # Süt mamulleri / tatlı shop names
    if re.search(r"sut\s*mamul|tatli|pastane|firin", header):
        scores["Food"] += 4

    return scores


def _pick_by_priority(
    scores: dict[str, int],
    *,
    prefer_food_over_groceries: bool = False,
) -> Optional[str]:
    if not any(v > 0 for v in scores.values()):
        return None
    max_score = max(scores.values())
    tied = [c for c, v in scores.items() if v == max_score]
    if prefer_food_over_groceries and "Food" in tied and "Groceries" in tied:
        return "Food"
    for cat in CATEGORY_PRIORITY:
        if cat in tied:
            return cat
    return tied[0]


def detect_receipt_category(
    raw_text: str,
    merchant: Optional[str] = None,
    lines: Optional[list[str]] = None,
) -> tuple[Optional[str], str]:
    """
    Returns (category_name, source) where source is unit|priority|keywords|store.
    """
    lines = lines or [ln.strip() for ln in raw_text.splitlines() if ln.strip()]
    text = normalize_text(raw_text)
    merchant_norm = normalize_text(merchant or "")
    header = normalize_text("\n".join(lines[:8]))

    # Tier 1 — absolute transport / health
    if _tier1_transport(text, header):
        return "Transport", "priority"
    if _tier1_health(text):
        return "Health", "priority"

    restaurant = _is_restaurant_receipt(text, header, lines)

    # Store name hints (before units, after tier1)
    store = _store_category(header, merchant_norm)
    if store == "Groceries" and not _FOOD_PRODUCT_LINE_RE.search(header):
        return "Groceries", "store"
    if store == "Transport":
        return "Transport", "store"
    if store == "Health":
        return "Health", "store"

    if restaurant:
        return "Food", "keywords"

    # Step 1 — units on product lines (lt/kg/paket → market; menü adet → yemek)
    unit_scores = _score_units(lines)
    g, f = unit_scores["Groceries"], unit_scores["Food"]
    if g > 0 or f > 0:
        if f > g:
            return "Food", "unit"
        if g > f:
            return "Groceries", "unit"
        if f > 0:
            return "Food", "unit"
        if g > 0:
            return "Groceries", "unit"

    # Step 2 — keywords + priority order
    kw_scores = _score_keywords(text, lines, merchant_norm)

    if _RESTAURANT_HEADER_RE.search(header) or re.search(
        r"kebap|restoran|lokanta|sut\s*mamul", header
    ):
        kw_scores["Food"] += 8

    if store == "Groceries":
        kw_scores["Groceries"] += 3

    winner = _pick_by_priority(kw_scores, prefer_food_over_groceries=restaurant)
    if winner:
        return winner, "keywords"

    return None, "none"
