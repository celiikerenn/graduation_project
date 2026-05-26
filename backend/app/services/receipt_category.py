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
    r"(?:\d+[\s,.]?\d*)\s*(?:lt|ml|kg|gr|g|gram|kilo)\b|"
    r"\b(?:lt|ml|kg|gr|g|kilo)\s*\d|"
    r"tl\s*/\s*kilo|"
    r"\d+\s*(?:paket|kutu|sise|po[sş]et|rulo|top|torba)\b|"
    r"\d+\s*['\u2019]?\s*li\s+paket|"
    r"\bpaket\b"
    r")",
    re.IGNORECASE,
)

_DAIRY_SHOP_RE = re.compile(r"\bsut\s*mamul", re.IGNORECASE)

_GROCERY_STAPLE_RE = re.compile(
    r"\b(?:zeytin|peynir|kasar|lor|tereyag|yogurt|ayran)\b",
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
    r"\b(?:tatli|yiy?ecek|icecek|kebap|kebapci|pide|lahmacun|pizza|doner|döner|"
    r"corba|pilav|makarna|kofte|iskender|borek|waffle|dondurma|"
    r"sutlac|kunefe|baklava|sandvic|tost|durum|petifur|petit|bardak|cay|kahve|"
    r"chocolate|cikolata|sarayi|hamburger|burger|patates|menu)\b",
    re.IGNORECASE,
)

_FAST_FOOD_RE = re.compile(
    r"\b(?:hamburger|burger|kizilkayalar|bufe|bistro|fast\s*food|"
    r"mcdonald|burger\s*king|komagene|dominos|pizza\s*hut)\b",
    re.IGNORECASE,
)

# Turkish fiscal line: YİYECEK %10 (not supermarket discount "%2,00")
_FOOD_SERVICE_LINE_RE = re.compile(
    r"\b(?:yiy?ecek|icecek)\s*%?\s*\d",
    re.IGNORECASE,
)

_SUPERMARKET_CHAIN_RE = re.compile(
    r"\b(?:migros|bim|a101|sok|carrefour|carrefoursa|parrefour|hakmar|makro|kiler|file\s*market)\b",
    re.IGNORECASE,
)

_RESTAURANT_HEADER_RE = re.compile(
    r"(?:kebapci|kebap|restoran|rest\.?\s*hiz|lokanta|cafe|kafeterya|bistro|"
    r"yiy?ecek|pastane|firin|doner|döner|pide|lahmacun|"
    r"chocolate|cikolata|sarayi|hamburger|burger|kizilkayalar|bufe)",
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
    r"\b(?:migros|bim|a101|sok|carrefour|carrefoursa|parrefour|hakmar|makro|kiler|file|"
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
        "chocolate", "cikolata", "sarayi", "cikolata sarayi",
        "hamburger", "burger", "kizilkayalar", "bufe", "menu",
    ),
    "Groceries": (
        "market", "supermarket", "manav", "kasap", "migros", "bim", "a101", "sok",
        "carrefour", "hakmar", "makro", "kiler", "file", "zeytin", "yogurt", "kefir", "peynir",
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
    # OCR often reads YİYECEK as YIYECEK (single i)
    t = re.sub(r"\byiyecek\b", "yiyecek", t)
    t = re.sub(r"\bparrefour\b", "carrefour", t)
    t = re.sub(r"\bcarrefoursa\b", "carrefour", t)
    t = re.sub(r"\s+", " ", t)
    return t.strip()


def has_strong_food_signal(text: str, merchant: str = "") -> bool:
    """Restoran / büfe / fast-food — Groceries veya gida hafızası ezmesin."""
    blob = f"{text} {normalize_text(merchant)}"
    if _FAST_FOOD_RE.search(blob) or _RESTAURANT_HEADER_RE.search(blob):
        return True
    if _FOOD_SERVICE_LINE_RE.search(blob):
        return True
    if re.search(
        r"hamburger|burger|kizilkayalar|bufe|kebap|doner|pide|lahmacun|"
        r"restoran|lokanta|yiy?ecek",
        blob,
    ):
        return True
    return False


def has_strong_grocery_signal(text: str, lines: Optional[list[str]] = None) -> bool:
    """Market zinciri veya tipik süpermarket fişi — Food/hafıza ezmesin."""
    if _SUPERMARKET_CHAIN_RE.search(text) or _STORE_GROCERIES_RE.search(text):
        return True
    if re.search(r"carrefoursa\s+kart|csa\s+kart", text):
        return True
    lines = lines or []
    if _is_supermarket_receipt(text, lines):
        return True
    kg_lines = sum(
        1 for ln in lines if re.search(r"\d+[.,]?\d*\s*kg\b", normalize_text(ln), re.IGNORECASE)
    )
    if kg_lines >= 1 and re.search(r"\b(?:parrefour|carrefour|un|ceviz|deterjan)\b", text):
        return True
    return False


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

    if _GROCERY_STAPLE_RE.search(norm):
        return "Groceries"

    if re.search(r"\bsut\b", norm) and re.search(
        r"\d+\s*(?:lt|ml)|\d\s*l\b|3lt|/\s*adet|adet\s*x",
        norm,
    ):
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


def _classify_dairy_shop(lines: list[str], text: str) -> Optional[str]:
    """Süt mamülleri dükkanı: paket süt/zeytin → Groceries; hazır tatlı (adet) → Food."""
    if not _DAIRY_SHOP_RE.search(text):
        return None
    has_adet_pricing = any(
        _MENU_ADET_PRICE_RE.search(ln) or _ADET_LINE_RE.search(ln) for ln in lines
    )
    food_items = 0
    grocery_items = 0
    for line in lines:
        if not _line_looks_like_product(line):
            continue
        norm = normalize_text(line)
        if re.search(r"\btatli\b", norm) and (
            _ADET_LINE_RE.search(norm)
            or _MENU_ADET_PRICE_RE.search(norm)
            or re.search(r"\badet\b", norm)
            or has_adet_pricing
        ):
            food_items += 1
            continue
        if _GROCERY_STAPLE_RE.search(norm) or re.search(r"kilo\b", norm, re.IGNORECASE):
            grocery_items += 1
            continue
        if re.search(r"\bsut\b", norm):
            grocery_items += 1
    if food_items > 0 and grocery_items == 0:
        return "Food"
    if grocery_items > 0:
        return "Groceries"
    return None


def _is_supermarket_receipt(text: str, lines: list[str]) -> bool:
    if _SUPERMARKET_CHAIN_RE.search(text):
        return True
    if re.search(r"carrefoursa\s+kart|csa\s+kart", text):
        return True
    kg_lines = sum(
        1 for ln in lines if re.search(r"\d+[.,]?\d*\s*kg\b", normalize_text(ln), re.IGNORECASE)
    )
    if kg_lines >= 1 and re.search(r"\b(?:parrefour|carrefour|un|ceviz|deterjan|sut)\b", text):
        return True
    if kg_lines >= 2 and re.search(r"\b(?:un|ceviz|yogurt|deterjan|sut)\b", text):
        return True
    return False


def _is_restaurant_receipt(text: str, header: str, lines: list[str]) -> bool:
    """Restoran / kafe / kebapçı fişi — adet satırları market sayılmasın."""
    if _SUPERMARKET_CHAIN_RE.search(text) or _STORE_GROCERIES_RE.search(text):
        return False
    if _STORE_GROCERIES_RE.search(header):
        return False
    if _RESTAURANT_HEADER_RE.search(header):
        return True
    blob = normalize_text("\n".join(lines))
    if (
        _RESTAURANT_ITEM_RE.search(blob)
        or re.search(r"\byiy?ecek\b", blob)
        or _FOOD_SERVICE_LINE_RE.search(blob)
    ):
        return True
    if re.search(r"\bchocolate\b", blob) and re.search(r"\bsarayi\b", blob):
        return True
    menu_lines = sum(
        1
        for ln in lines
        if _MENU_ADET_PRICE_RE.search(ln) and re.search(r"\d+[.,]\d{2}", ln)
    )
    if menu_lines >= 2:
        return True
    return False


def _store_category(header: str, merchant: str, full_text: str = "") -> Optional[str]:
    blob = f"{header} {merchant} {full_text}"
    if _STORE_GROCERIES_RE.search(blob) or _SUPERMARKET_CHAIN_RE.search(blob):
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

    if re.search(r"tatli|pastane|firin", header) and not _DAIRY_SHOP_RE.search(header):
        scores["Food"] += 4
    if _DAIRY_SHOP_RE.search(header):
        has_tatli_items = any(
            re.search(r"\btatli\b", normalize_text(ln)) and _ADET_LINE_RE.search(normalize_text(ln))
            for ln in lines
            if _line_looks_like_product(ln)
        )
        if has_tatli_items:
            scores["Food"] += 4
        else:
            scores["Groceries"] += 3

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

    if _FAST_FOOD_RE.search(f"{header} {merchant_norm} {text}"):
        return "Food", "keywords"

    if _is_supermarket_receipt(text, lines):
        return "Groceries", "store"

    dairy = _classify_dairy_shop(lines, text)
    if dairy:
        return dairy, "unit"

    restaurant = _is_restaurant_receipt(text, header, lines)
    if not restaurant and re.search(r"\bchocolate\b", f"{header} {merchant_norm}"):
        if not _SUPERMARKET_CHAIN_RE.search(text):
            restaurant = True
    if not restaurant and _FOOD_SERVICE_LINE_RE.search(text):
        restaurant = True

    # Store name hints (before units, after tier1) — chain name may be at bottom of receipt
    store = _store_category(header, merchant_norm, text)
    if store == "Groceries" and not _FOOD_PRODUCT_LINE_RE.search(header):
        return "Groceries", "store"
    if store == "Transport":
        return "Transport", "store"
    if store == "Health":
        return "Health", "store"

    unit_scores = _score_units(lines)

    if restaurant and unit_scores["Groceries"] > unit_scores["Food"]:
        if _SUPERMARKET_CHAIN_RE.search(text) or unit_scores["Groceries"] >= 2:
            restaurant = False

    if restaurant:
        return "Food", "keywords"

    # Step 1 — units on product lines (lt/kg/paket → market; menü adet → yemek)
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
        r"kebap|restoran|rest\.?\s*hiz|lokanta|chocolate|sarayi", header
    ):
        kw_scores["Food"] += 8

    if store == "Groceries":
        kw_scores["Groceries"] += 3

    winner = _pick_by_priority(kw_scores, prefer_food_over_groceries=restaurant)
    if winner:
        return winner, "keywords"

    if has_strong_food_signal(text, merchant_norm):
        return "Food", "keywords"

    return None, "none"
