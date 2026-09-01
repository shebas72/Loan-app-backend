# Multi-Tenant Loan Origination & Underwriting Platform

A portfolio project simulating a real enterprise loan origination system for banks and financial institutions — built as a decoupled Laravel API + Next.js frontend, with multi-tenancy, role-based access control, and an enforced workflow/audit trail.

This project was built as both a working application and a training curriculum — every module below doubles as a lesson, complete with the real debugging issues hit along the way (see [`TROUBLESHOOTING.md`](./TROUBLESHOOTING.md)).

## Why this project

Most portfolio CRUD apps demonstrate that you can move data in and out of a database. This project is built specifically to demonstrate the things that separate "a working app" from "software a bank would actually deploy":

- **Multi-tenancy** — one platform, many banks, zero data leakage between them, enforced at the query layer
- **Role-based authorization** — five distinct roles (Applicant, Loan Officer, Underwriter, Branch Manager, Admin), each with different permissions on the same resources
- **An enforced workflow** — loan applications move through a defined state machine (draft → submitted → under_review → approved/rejected → disbursed), not an arbitrary status field
- **A real audit trail** — every status change is permanently logged: who changed it, when, from what, to what, and why
- **A decoupled architecture** — Laravel serves a pure JSON API; Next.js consumes it, the way a real bank might reuse the same backend for a web portal and a future mobile app

## Tech stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (API-only, Sanctum token auth) |
| Frontend | Next.js (App Router) + TypeScript + Tailwind CSS |
| Database | MySQL (InnoDB) |
| Auth | Laravel Sanctum (Bearer token) |
| Authorization | Laravel Policies + a custom tenant-scoping global scope |

## Architecture overview

```
loan-platform/
├── backend/     ← Laravel API (routes/api.php, no Blade views)
├── frontend/    ← Next.js app (App Router, TypeScript)
└── docs/        ← this folder
```

The backend and frontend are two independently deployable applications communicating over HTTP/JSON. The backend has zero knowledge of how it's rendered; the frontend has zero direct database access. See [`ARCHITECTURE.md`](./ARCHITECTURE.md) for the reasoning behind this and other major decisions.

## Core domain model

- **Tenant** — a bank/financial institution using the platform
- **User** — belongs to a tenant (except platform Admins); has a role
- **LoanApplication** — the central record; belongs to a tenant and an applicant
- **Document** — uploaded KYC/financial files attached to a loan application
- **StatusTransition** — an immutable audit log entry for every status change
- **CreditScoreResult** — output of the rules-based scoring engine

## Local setup (native, no Docker)

### Requirements
- PHP 8.2+, Composer 2.x
- Node 20+, npm
- MySQL/MariaDB with **InnoDB as the default storage engine** (see note below if using WAMP)

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Set your `.env` database credentials, then:

```bash
php artisan migrate
php artisan serve
```

Backend runs at `http://127.0.0.1:8000`.

**⚠️ WAMP users:** WAMP's default MySQL/MariaDB storage engine is often `MyISAM`, not `InnoDB`. This breaks migrations with composite indexes (e.g. `failed_jobs`). Fix: in `config/database.php`, under the `mysql` connection array, set `'engine' => 'InnoDB'`. See [`TROUBLESHOOTING.md`](./TROUBLESHOOTING.md) for the full story.

### Frontend

```bash
cd frontend
npm install
```

Create `.env.local`:

```
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
```

```bash
npm run dev
```

Frontend runs at `http://localhost:3000`.

## API documentation

See [`API.md`](./API.md) for the full endpoint reference.

## Roles and permissions summary

| Role | Can create loans | Can edit own draft | Can process/review | Can approve | Can delete |
|---|---|---|---|---|---|
| Applicant | ✅ | ✅ (draft only) | ❌ | ❌ | ❌ |
| Loan Officer | ❌ | ❌ | ✅ | ❌ | ❌ |
| Underwriter | ❌ | ❌ | ✅ | ✅ | ❌ |
| Branch Manager | ❌ | ❌ | ✅ | ✅ | ❌ |
| Admin | ❌ | ❌ | ❌ | ❌ | ✅ |

Full authorization logic lives in `app/Policies/LoanApplicationPolicy.php`.

## Project status

Built as a module-by-module curriculum. See progress and design log in `docs/` and inline code comments.

- ✅ Module 1 — Database design & multi-tenant schema
- ✅ Module 2 — Auth (Sanctum) & RBAC (Policies + tenant scoping)
- ✅ Module 3 — Core CRUD (Loan Applications)
- ✅ Module 4 — Workflow / state machine + audit trail
- ⬜ Module 5 — Next.js frontend
- ⬜ Module 6 — Underwriter dashboard
- ⬜ Module 7 — Queues & notifications
- ⬜ Module 8 — Testing
- ⬜ Module 9 — CI/CD & deployment
