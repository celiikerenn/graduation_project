"""
Server-side chart PNG generation for PDF reports (Pillow only).
"""
from __future__ import annotations

import base64
import io

from PIL import Image, ImageDraw, ImageFont


def _fig_to_base64(img: Image.Image) -> str:
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    buf.seek(0)
    return base64.b64encode(buf.read()).decode("ascii")


def _load_font(size: int = 12):
    try:
        return ImageFont.truetype("arial.ttf", size)
    except OSError:
        return ImageFont.load_default()


def _palette(n: int) -> list[tuple[int, int, int]]:
    base = [
        (37, 99, 235),
        (16, 185, 129),
        (245, 158, 11),
        (239, 68, 68),
        (139, 92, 246),
        (236, 72, 153),
        (6, 182, 212),
        (132, 204, 22),
        (249, 115, 22),
        (107, 114, 128),
    ]
    return [base[i % len(base)] for i in range(n)]


def render_pie_chart(category_totals: dict[str, float]) -> str | None:
    labels = list(category_totals.keys())
    values = [float(category_totals[k]) for k in labels]
    total = sum(values)
    if not labels or total <= 0:
        return None

    w, h = 640, 400
    img = Image.new("RGB", (w, h), "white")
    draw = ImageDraw.Draw(img)
    font = _load_font(11)
    title_font = _load_font(14)

    draw.text((20, 12), "Spending by category", fill=(15, 23, 42), font=title_font)

    cx, cy, r = 200, 210, 120
    colors = _palette(len(labels))
    start = -90.0
    for i, val in enumerate(values):
        sweep = 360.0 * val / total
        end = start + sweep
        draw.pieslice(
            (cx - r, cy - r, cx + r, cy + r),
            start,
            end,
            fill=colors[i],
            outline="white",
        )
        start = end

    ly = 40
    for i, label in enumerate(labels):
        pct = 100.0 * values[i] / total
        draw.rectangle((380, ly, 395, ly + 12), fill=colors[i])
        text = f"{label[:18]} — {pct:.1f}%"
        draw.text((402, ly - 1), text, fill=(51, 65, 85), font=font)
        ly += 22

    return _fig_to_base64(img)


def render_bar_chart(category_totals: dict[str, float]) -> str | None:
    if not category_totals:
        return None

    items = sorted(((k, float(v)) for k, v in category_totals.items() if float(v) > 0), key=lambda x: x[1])
    if not items:
        return None

    labels = [k for k, _ in items]
    values = [v for _, v in items]
    max_val = max(values)

    row_h = 28
    pad_top = 36
    pad_left = 120
    pad_right = 80
    chart_w = 520
    h = pad_top + len(labels) * row_h + 16
    w = pad_left + chart_w + pad_right

    img = Image.new("RGB", (w, h), "white")
    draw = ImageDraw.Draw(img)
    font = _load_font(11)
    title_font = _load_font(14)
    colors = _palette(len(labels))

    draw.text((20, 10), "Category comparison", fill=(15, 23, 42), font=title_font)

    bar_max_w = chart_w - 20
    for i, (label, val) in enumerate(items):
        y = pad_top + i * row_h
        color = colors[i]
        draw.text((8, y + 6), label[:14], fill=(51, 65, 85), font=font)
        bar_len = int((val / max_val) * bar_max_w) if max_val > 0 else 0
        x0 = pad_left
        y0 = y + 4
        draw.rounded_rectangle(
            (x0, y0, x0 + max(bar_len, 2), y0 + 18),
            radius=4,
            fill=color,
        )
        draw.text((x0 + bar_max_w + 8, y + 6), f"{val:,.2f}", fill=(51, 65, 85), font=font)

    return _fig_to_base64(img)


def build_report_chart_images(category_totals: dict[str, float]) -> dict[str, str | None]:
    pie_b64 = None
    bar_b64 = None
    try:
        pie_b64 = render_pie_chart(category_totals)
    except Exception:
        pie_b64 = None
    try:
        bar_b64 = render_bar_chart(category_totals)
    except Exception:
        bar_b64 = None
    return {"pie_png_base64": pie_b64, "bar_png_base64": bar_b64}
