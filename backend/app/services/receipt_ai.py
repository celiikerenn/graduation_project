"""
Optional LLM helper to suggest merchant / description from receipt OCR text.
Requires OPENAI_API_KEY in backend/.env; falls back to OCR heuristics when unset.
"""
from __future__ import annotations

import json
import os
import re
import urllib.error
import urllib.request
from typing import Optional


def _clean_description(value: str) -> Optional[str]:
    text = re.sub(r"\s+", " ", value.strip())
    if len(text) < 2:
        return None
    return text[:120]


def _suggest_via_llm(raw_text: str) -> Optional[str]:
    api_key = os.getenv("OPENAI_API_KEY", "").strip()
    if not api_key:
        return None

    model = os.getenv("OPENAI_MODEL", "gpt-4o-mini").strip() or "gpt-4o-mini"
    snippet = raw_text.strip()[:3500]

    payload = {
        "model": model,
        "messages": [
            {
                "role": "system",
                "content": (
                    "You read noisy OCR text from Turkish or English receipts. "
                    "Reply with ONLY the store or merchant name (max 80 characters). "
                    "No quotes, no explanation, no JSON."
                ),
            },
            {
                "role": "user",
                "content": f"OCR text:\n{snippet}",
            },
        ],
        "temperature": 0.2,
        "max_tokens": 60,
    }

    req = urllib.request.Request(
        "https://api.openai.com/v1/chat/completions",
        data=json.dumps(payload).encode("utf-8"),
        headers={
            "Content-Type": "application/json",
            "Authorization": f"Bearer {api_key}",
        },
        method="POST",
    )

    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            body = json.loads(resp.read().decode("utf-8"))
        content = body["choices"][0]["message"]["content"]
        return _clean_description(content)
    except (urllib.error.URLError, TimeoutError, KeyError, IndexError, json.JSONDecodeError):
        return None


def _is_weak_merchant(merchant: str) -> bool:
    if re.search(r"\d{4,}", merchant):
        return True
    if re.search(
        r"(?i)(fatura|receipt|fiş|fis|vergi|v\.?d\.?|tckn|www\.|http|@|toplam|kdv|tutar)",
        merchant,
    ):
        return True
    letters = sum(c.isalpha() for c in merchant)
    if letters < max(3, len(merchant) * 0.35):
        return True
    return False


def suggest_description(raw_text: str, merchant: Optional[str] = None) -> tuple[Optional[str], Optional[str]]:
    """
    Returns (description, source) where source is 'ai', 'ocr', or None.
    """
    if merchant:
        cleaned = _clean_description(merchant)
        if cleaned and not _is_weak_merchant(cleaned):
            return cleaned, "ocr"

    if not raw_text.strip():
        return None, None

    llm = _suggest_via_llm(raw_text)
    if llm:
        return llm, "ai"

    if merchant:
        cleaned = _clean_description(merchant)
        if cleaned:
            return cleaned, "ocr"

    return None, None
