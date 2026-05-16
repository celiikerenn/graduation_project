"""
Personal Finance Tracker - FastAPI Service Layer
Tüm iş mantığı ve MySQL erişimi bu katmanda.
Laravel sadece HTTP istekleri ile bu API'yi kullanır.
"""
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.routers import expenses, categories, auth, receipts
from app.database import Base, engine, SessionLocal
import app.models_db  # noqa: F401  (Base metadata'ya modelleri kaydetmek için)

app = FastAPI(
    title="Personal Finance Tracker API",
    description="Harcama CRUD ve aylık özet. Laravel web katmanı bu API'yi kullanır.",
    version="1.0.0",
)

# Laravel farklı portta çalışacağı için CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(expenses.router, prefix="/api")
app.include_router(categories.router, prefix="/api")
app.include_router(auth.router, prefix="/api")
app.include_router(receipts.router, prefix="/api")

DEFAULT_CATEGORIES = [
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
]

# Eski Türkçe isimler → İngilizce (tek seferlik taşıma / birleştirme)
CATEGORY_TR_TO_EN = {
    "Yemek": "Food",
    "Ulaşım": "Transport",
    "Kira": "Rent",
    "Faturalar": "Utilities",
    "Market": "Groceries",
    "Sağlık": "Health",
    "Eğitim": "Education",
    "Eğlence": "Entertainment",
    "Giyim": "Clothing",
    "Diğer": "Other",
}


def _migrate_category_names_tr_to_en(db) -> None:
    """Türkçe kategori satırlarını İngilizce adlara taşır; hedef ad zaten varsa harcamalar birleştirilir."""
    from app.models_db import Expense, ExpenseCategory

    for tr_name, en_name in CATEGORY_TR_TO_EN.items():
        old_cat = db.query(ExpenseCategory).filter(ExpenseCategory.name == tr_name).first()
        if old_cat is None:
            continue
        en_cat = db.query(ExpenseCategory).filter(ExpenseCategory.name == en_name).first()
        if en_cat is None:
            old_cat.name = en_name
        elif en_cat.id != old_cat.id:
            db.query(Expense).filter(Expense.category_id == old_cat.id).update(
                {Expense.category_id: en_cat.id},
                synchronize_session=False,
            )
            db.delete(old_cat)
    db.commit()


@app.on_event("startup")
def startup_init_db():
    """
    Uygulama açılışında MySQL tablolarını oluşturur (yoksa) ve varsayılan kategorileri ekler.
    Alembic/migration kullanılmayan basit kurulum senaryosu için.
    """
    Base.metadata.create_all(bind=engine)

    db = SessionLocal()
    try:
        _migrate_category_names_tr_to_en(db)
        _ensure_default_categories(db)
    finally:
        db.close()


def _ensure_default_categories(db) -> None:
    """Varsayılan kategori adları eksikse ekler (tablo boşalsa veya yanlışlıkla silinse bile)."""
    from app.models_db import ExpenseCategory

    for name in DEFAULT_CATEGORIES:
        exists = db.query(ExpenseCategory).filter(ExpenseCategory.name == name).first()
        if exists is None:
            db.add(ExpenseCategory(name=name))
    db.commit()


@app.get("/")
def root():
    return {"service": "Finance Tracker API", "docs": "/docs"}


@app.get("/api/health")
def health():
    return {"status": "ok"}


@app.get("/api/health/db")
def health_db():
    """
    MySQL bağlantısını dener. Hata varsa gerçek hata mesajını döner (sorun tespiti için).
    Tarayıcıda http://127.0.0.1:8001/api/health/db açarak kontrol edin.
    """
    try:
        from sqlalchemy import text
        from app.database import engine
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
        return {"status": "ok", "database": "connected"}
    except Exception as e:
        return {
            "status": "error",
            "database": "failed",
            "message": str(e),
        }
