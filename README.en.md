# GamificaEdu 🚀

🌐 [Português (Brasil)](README.md) · **English**

> **Gamified Learning & Multidisciplinary Teaching Management Platform**

GamificaEdu is a modern ecosystem for K-12 and higher education, built with **Laravel 13**, **InertiaJS** and **Vue 3**. The platform uses gamification to engage students through points (XP), learning tracks, classes and leaderboards — with **internationalization (pt_BR / en)** support.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 13 (PHP 8.4+)
- **Frontend**: Vue 3 (Composition API) with InertiaJS (SPA)
- **Styling**: TailwindCSS (premium, responsive dark theme)
- **Validation/DTO**: Form Requests + `spatie/laravel-data`
- **Queue/Jobs**: Laravel Queue (Database / Redis)
- **Environment**: Docker via **Laravel Sail** (PHP 8.5, MySQL 8.4, Redis, Mailpit)
- **Supported Databases**: MySQL / PostgreSQL / SQLite
- **i18n**: `lang/` files (pt_BR and en) exposed to the frontend via the `__()` helper
- **Quality**: Pest (113 tests), PHPStan (level 5 / Larastan), Laravel Pint, ESLint + Prettier

---

## 🏛️ Architecture

The backend follows a senior single-responsibility pattern:

- **Single-action controllers** (`__invoke`) — one action per controller.
- **Form Requests** for validation and authorization.
- **Resources** for response serialization (Inertia).
- **Actions** for database writes (transactional cases).
- **Services** for integrations and orchestration (e.g. dashboards, ranking, email).
- **Enums** (`UserRole`, `GeneralStatus`) for data integrity.
- **Lean models** with reusable scopes and traits (e.g. `HasRoles`).

---

## 🌟 Key Features

### 👑 Super Administrator
- **Multi-institution**: CRUD of schools/universities (with CNPJ validation).
- **Global management**: users, subjects and **classes** across all institutions.
- **Student↔class link**: when creating/editing a student, can enroll them in a class.
- **Queued log pruning**, **access metrics** (encrypted IP), **failed jobs** (retry/delete) and **impersonation** for support.

### 🏫 Institution Administrator
- **Context switching** between managed institutions.
- **Teacher & student management** (CRUD with reactive search by name, email and class).
- **Subjects** with teacher assignment.
- **Classes**: create institution classes, assign **one teacher** and several **subjects**, and enroll students.

### 👨‍🏫 Teacher
- **Dedicated sidebar**: My Classes, My Students and Subjects.
- **Dashboard with real, reactive metrics** (classes, students, subjects) + **performance charts** (per class and per student), auto-refreshing.
- **Student registration** and enrollment into one of their classes.
- **Subject management**: materials (readings), tests (quizzes) and a question bank, plus automatic content generation (mock AI).

### 🎓 Student
- **Learning track**: dynamic subject progress.
- **Interactive activities**: quizzes with automatic grading and XP proportional to correct answers.
- **Leaderboard**: Global, per Institution and per Subject, with pagination, sorting and search.

---

## 🧩 Classes

The layer connecting teachers, subjects and students:

- A **class belongs to an institution** and has **at most one teacher**.
- A **teacher can have many classes**.
- A **class can have many subjects**.
- **Students are enrolled in classes** (teacher, admin or super admin can link them).

---

## 🔑 Advanced Authentication

- **Magic Login Token**: access via a single-use signed link sent by email (expires in 15 min).
- **Remember Me** integrated into the login flow.
- **Single-session control**: a new login ends previous sessions.
- **Force password change** on first access for admin-created accounts.

---

## ⚡ Setup & Run (Laravel Sail — recommended)

The project runs in Docker containers via Laravel Sail.

```bash
# 1. Environment variables
cp .env.example .env

# 2. Install PHP dependencies (via an ephemeral container)
docker run --rm -v $(pwd):/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install

# 3. Start the containers (app, mysql, redis, mailpit)
./vendor/bin/sail up -d

# 4. App key, migrations and demo seed
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 5. Frontend dependencies and build
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

> The queue runs via `QUEUE_CONNECTION` (database/redis). To process emails and background jobs:
> ```bash
> ./vendor/bin/sail artisan queue:work
> ```

### Without Sail (local PHP 8.4+)

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan queue:work      # in one terminal
php artisan serve           # in another
npm run dev                 # in another
```

---

## 🧪 Quality & Tests

With Sail (prefix `./vendor/bin/sail`) or locally:

```bash
# Tests (Pest)
php artisan test

# Code style (Laravel Pint)
./vendor/bin/pint            # fixes
./vendor/bin/pint --test     # checks only

# Static analysis (PHPStan / Larastan, level 5)
./vendor/bin/phpstan analyse

# Frontend lint (ESLint + Prettier)
npm run lint                 # eslint --fix on resources/js
```

### Continuous Integration

The `.github/workflows/quality.yml` workflow runs, on every push/PR: dependency install, **Pint**, **PHPStan**, **ESLint**, the Vite build and the **Pest** suite (PHP 8.4).

---

## 🌍 Internationalization (i18n)

- UI strings live in `lang/pt_BR/*.php` and `lang/en/*.php` (groups: `ui`, `nav`, `admin`, `teacher`, `superadmin`, `student`, `misc`, `classrooms`).
- The backend shares translations via Inertia; the frontend uses the global **`__('group.key')`** helper (mirroring Laravel's `__()`), with placeholder support (`:name`).

---

Built to revolutionize educational engagement with modern development best practices. 🌟
