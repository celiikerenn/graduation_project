# Personal Finance Tracker

Graduation project: a **personal expense tracking** web application. The **Laravel** app is the browser UI; the **FastAPI** service owns business logic and persists data in **MySQL**. There is no direct Laravel–database coupling for expense data—all CRUD goes through the API.

## Architecture

| Layer        | Stack        | Role |
|-------------|--------------|------|
| Web UI      | Laravel 12, PHP 8.2 | Sessions, Blade views, PDF/CSV reports |
| API & domain| FastAPI, SQLAlchemy   | Auth, expenses, categories |
| Database    | MySQL                 | Users, expenses, categories |

Default local URLs:

- Laravel: `http://127.0.0.1:8000`
- FastAPI: `http://127.0.0.1:8001` (set `FASTAPI_URL` in Laravel’s `.env`)

## Features (high level)

- Register / login (session-based)
- Dashboard, charts, expense list with filters, create/edit/delete
- Monthly reports (CSV/PDF), profile and budget settings

## Repository layout

```
graduation_project/
├── backend/          # FastAPI service (Python)
│   ├── .env.example
│   └── app/          # routers, models
├── laravel_app/      # Laravel web application
│   ├── .env.example
│   └── ...
└── README.md
```

## Prerequisites

- **PHP** ^8.2, **Composer**
- **Python** 3.11+ (recommended)
- **MySQL** (e.g. XAMPP) and an empty database (e.g. `finance_tracker`)
## Backend (FastAPI)

```bash
cd backend
python -m venv .venv
# Windows:
.\.venv\Scripts\pip install -r requirements.txt
copy .env.example .env
# Edit .env: MYSQL_* (bkz. backend/.env.example)
uvicorn app.main:app --reload --host 127.0.0.1 --port 8001
```

- API docs: `http://127.0.0.1:8001/docs`
- DB health: `http://127.0.0.1:8001/api/health/db`

On startup, default expense categories are ensured if the table is empty or incomplete.

## Laravel app

```bash
cd laravel_app
composer install
copy .env.example .env   # Windows; use cp on Unix
php artisan key:generate
php artisan migrate
```

Configure at least:

- `FASTAPI_URL=http://127.0.0.1:8001` (must match the running API)
- Laravel uses SQLite by default in `.env.example` for its **own** tables (sessions, cache, etc.); expense data still lives in MySQL via FastAPI.

Run the dev server:

```bash
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Running both services

1. Start MySQL and create the database from `backend/.env`.
2. Start FastAPI on port **8001**.
3. Start Laravel on port **8000** with `FASTAPI_URL` pointing to FastAPI.

## License

This project is provided as-is for academic / portfolio use. Adjust licensing if you publish it formally.
