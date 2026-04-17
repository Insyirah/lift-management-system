# Lift Inspection System

A full-stack web application for managing lift/elevator inspections. Built with Laravel 13 (REST API) and Vue 3 (SPA frontend), it supports multi-tenant company structures, role-based access control, inspection checklists, and PDF report generation.

---

## Features

- **Multi-tenant** — Supports multiple inspection companies, each with their own organisations, buildings, and lifts
- **Role-based access control** — Three roles: `super_admin`, `admin`, `inspector`
- **Inspection workflow** — Schedule inspections, fill checklist results (pass/fail/N/A), upload photos, and mark inspections complete
- **PDF reports** — Generate and download inspection reports
- **Dashboard** — Stats and overview per role
- **Token-based authentication** — Laravel Sanctum

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Vue 3.4, Vue Router 4, Pinia 2 |
| Authentication | Laravel Sanctum |
| Styling | Tailwind CSS 3.4 |
| Build Tool | Vite 5 |
| PDF Generation | barryvdh/laravel-dompdf |
| Database | SQLite (default) / MySQL / PostgreSQL |
| HTTP Client | Axios |
| Icons | Heroicons Vue |

---

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- SQLite (default) or a supported database server

---

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd lift-inspection-system
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure the database

The default configuration uses **SQLite**. The database file will be created automatically at `database/database.sqlite`.

To use MySQL or PostgreSQL instead, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lift_inspection
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Create a storage symlink

```bash
php artisan storage:link
```

### 8. Build frontend assets

```bash
# Development (with hot reload)
npm run dev

# Production
npm run build
```

### 9. Start the server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

---

## Environment Variables

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `Laravel` |
| `APP_URL` | Base URL of the application | `http://localhost` |
| `DB_CONNECTION` | Database driver (`sqlite`, `mysql`, `pgsql`) | `sqlite` |
| `FILESYSTEM_DISK` | Storage disk for uploads and reports | `local` |
| `SESSION_DRIVER` | Session storage driver | `database` |
| `CACHE_STORE` | Cache storage driver | `database` |
| `MAIL_MAILER` | Mail driver | `log` |
| `BCRYPT_ROUNDS` | Password hashing rounds | `12` |

---

## User Roles

| Role | Description |
|---|---|
| `super_admin` | Full access across all companies. Manages companies, all users, organisations, buildings, lifts, inspections, and reports. |
| `admin` | Scoped to their own company. Manages users, organisations, buildings, lifts, inspection items, inspections, and reports. |
| `inspector` | Views assigned inspections, records checklist results, uploads photos, and completes inspections. |

---

## API Overview

Base URL: `/api`

### Authentication

| Method | Endpoint | Description |
|---|---|---|
| POST | `/login` | Log in and receive an access token |
| POST | `/logout` | Revoke the current token |
| GET | `/me` | Get the authenticated user |

### Resources

| Resource | Endpoints | Roles |
|---|---|---|
| Dashboard | `GET /dashboard/stats` | All authenticated |
| Companies | `GET/POST /companies`, `PUT/DELETE /companies/{id}` | super_admin |
| Users | `GET/POST /users`, `PUT/DELETE /users/{id}` | super_admin, admin |
| Organisations | `GET/POST /organisations`, `PUT/DELETE /organisations/{id}` | super_admin, admin |
| Buildings | `GET/POST /buildings`, `PUT/DELETE /buildings/{id}` | super_admin, admin |
| Lifts | `GET/POST /lifts`, `PUT/DELETE /lifts/{id}` | super_admin, admin |
| Inspection Items | `GET/POST /inspection-items`, `PUT/DELETE /inspection-items/{id}` | super_admin, admin |
| Inspections | `GET/POST /inspections`, `GET/PUT/DELETE /inspections/{id}` | super_admin, admin (create/edit); all (view) |
| Results | `PUT /inspections/{id}/results/{result}` | super_admin, admin, inspector |
| Photo Upload | `POST /inspections/{id}/results/{result}/photo` | super_admin, admin, inspector |
| Bulk Results | `POST /inspections/{id}/results/bulk` | super_admin, admin, inspector |
| Complete Inspection | `POST /inspections/{id}/complete` | super_admin, admin, inspector |
| Generate Report | `POST /inspections/{id}/generate-report` | super_admin, admin |
| View Report | `GET /reports/{report}` | super_admin, admin |
| Download Report | `GET /reports/{report}/download` | super_admin, admin |

All endpoints (except `/login`) require the header:

```
Authorization: Bearer <token>
```

---

## Data Model

```
Companies
  └── Organisations
        └── Buildings
              └── Lifts
                    └── Inspections (assigned to a User/Inspector)
                          ├── InspectionResults (per InspectionItem)
                          │     └── Photo (optional)
                          └── InspectionReport (PDF)

Users (belong to a Company)
InspectionItems (reusable checklist templates with categories)
```

---

## Inspection Workflow

1. **Admin** creates an inspection, assigns it to an inspector, and sets a date and type (`routine`, `annual`, `special`, `follow_up`).
2. **Inspector** opens the inspection (status: `pending` → `in_progress`) and fills in each checklist item (`pass` / `fail` / `na`) with optional remarks and photos.
3. **Inspector** marks the inspection as complete (status: `completed` or `failed`).
4. **Admin / Super Admin** generates and downloads a PDF report.

---

## Project Structure

```
lift-inspection-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/API/     # API controllers
│   │   └── Middleware/          # Role-based middleware
│   └── Models/                  # Eloquent models
├── database/
│   └── migrations/              # Database schema
├── resources/
│   ├── js/
│   │   ├── api/                 # Axios instance & config
│   │   ├── components/          # Reusable Vue components
│   │   ├── layouts/             # App layout
│   │   ├── pages/               # Vue page components
│   │   ├── router/              # Vue Router configuration
│   │   ├── stores/              # Pinia stores
│   │   └── app.js               # Vue application entry point
│   ├── css/                     # Tailwind CSS
│   └── views/                   # Blade templates (SPA shell + report views)
├── routes/
│   ├── api.php                  # API route definitions
│   └── web.php                  # SPA catch-all route
├── .env.example
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
```

---

## Deployment (Render)

The project includes Docker-based deployment config for [Render](https://render.com) (free tier).

### Free tier notes
- Web service **sleeps after 15 min** of inactivity (slow first load ~30-60s)
- Free PostgreSQL database expires after **90 days**
- No credit card required

### Steps

1. Push your code to a GitHub repository.

2. Go to [render.com](https://render.com) and create a free account.

3. Click **New > Blueprint** and connect your GitHub repo. Render will detect `render.yaml` and auto-configure:
   - A web service (Docker, free plan)
   - A PostgreSQL database (free plan)
   - All environment variables (including `APP_KEY` auto-generated)

4. Click **Apply** and wait for the build to finish (~5 minutes on first deploy).

5. Your app will be live at `https://lift-inspection-system.onrender.com` (or similar).

### What happens on deploy
- Docker builds the image (installs PHP + Node dependencies, builds Vue assets)
- On container start: migrations run automatically, config/routes/views are cached

---

## Running Tests

```bash
php artisan test
```

---

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for PHP code formatting:

```bash
./vendor/bin/pint
```

---

## License

This project is proprietary software. All rights reserved.
