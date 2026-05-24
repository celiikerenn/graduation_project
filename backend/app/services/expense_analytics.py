"""
Expense analytics: spending anomalies, report helpers.
"""
from __future__ import annotations

from collections import defaultdict
from datetime import date
from typing import Any

from sqlalchemy.orm import Session, joinedload

from app.models_db import Expense


def _month_key(d: date) -> str:
    return f"{d.year:04d}-{d.month:02d}"


def _load_user_expenses(db: Session, user_id: int) -> list[Expense]:
    return (
        db.query(Expense)
        .options(joinedload(Expense.category))
        .filter(Expense.user_id == user_id)
        .all()
    )


def compute_spending_anomaly(db: Session, user_id: int) -> dict[str, Any]:
    """Compare this month's total spending to the average of the previous 3 full months."""
    today = date.today()
    current_key = _month_key(today)
    expenses = _load_user_expenses(db, user_id)

    by_month: dict[str, float] = defaultdict(float)
    for exp in expenses:
        if not exp.expense_date:
            continue
        by_month[_month_key(exp.expense_date)] += float(exp.amount or 0)

    sorted_months = sorted(by_month.keys())
    past_months = [m for m in sorted_months if m < current_key]
    baseline_months = past_months[-3:] if len(past_months) >= 3 else []

    current_total = round(by_month.get(current_key, 0.0), 2)

    if len(baseline_months) < 3:
        return {
            "month": current_key,
            "has_anomalies": False,
            "current_month_total": current_total,
            "baseline_average": 0.0,
            "increase_percent": 0.0,
        }

    baseline_vals = [by_month[m] for m in baseline_months]
    avg_baseline = sum(baseline_vals) / len(baseline_vals)

    if avg_baseline <= 0:
        increase_pct = 100.0 if current_total > 0 else 0.0
    else:
        increase_pct = ((current_total - avg_baseline) / avg_baseline) * 100.0

    has_anomaly = increase_pct > 50.0

    return {
        "month": current_key,
        "has_anomalies": has_anomaly,
        "current_month_total": current_total,
        "baseline_average": round(avg_baseline, 2),
        "increase_percent": round(increase_pct, 1),
    }


def category_spending_for_month(
    db: Session, user_id: int, year: int, month: int
) -> dict[str, float]:
    expenses = _load_user_expenses(db, user_id)
    out: dict[str, float] = defaultdict(float)
    for exp in expenses:
        if not exp.expense_date:
            continue
        if exp.expense_date.year != year or exp.expense_date.month != month:
            continue
        cat = exp.category.name if exp.category else "Other"
        out[cat] += float(exp.amount or 0)
    return dict(out)
