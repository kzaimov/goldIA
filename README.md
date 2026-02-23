# goldAI

goldAI is a Laminas MVC web application for tracking a personal gold investment portfolio.

## Stack

- Backend: Laminas MVC (PHP 8.3)
- Frontend: jQuery + Fomantic UI (CDN)
- Database: PostgreSQL
- Auth: Local session auth with role-based access (`admin`, `user`)

## Features

- Login/logout with per-user portfolio access
- Admin user management (add/delete users)
- Dashboard with portfolio totals (initial, current, profit/loss)
- Asset management (create assets with initial cost)
- Asset valuations by date (insert/update same date)
- Automatic yearly profit/loss calculation using:
  - each year’s last available valuation
  - first year baseline = initial cost
  - next years baseline = previous year-end value

## Database Setup

1. Create a PostgreSQL database named `goldai`.
2. Run schema script:

```bash
psql -U postgres -d goldai -f database/schema.sql
```

3. Run seed script:

```bash
psql -U postgres -d goldai -f database/seed.sql
```

Default seeded admin account:

- Email: `admin@goldai.local`
- Password: `admin123`

## App Configuration

1. Copy local config template:

```bash
copy config\autoload\local.php.dist config\autoload\local.php
```

2. Update PostgreSQL credentials in `config/autoload/local.php`.

## Run Locally

Install dependencies:

```bash
composer install
```

Run development server:

```bash
composer serve
```

Open:

- http://localhost:8080/login

## Tests

Run test suite:

```bash
vendor\bin\phpunit
```

## Main Routes

- `/login`
- `/logout`
- `/` (dashboard, requires login)
- `/portfolio/assets`
- `/portfolio/assets/add`
- `/portfolio/assets/:id`
- `/admin/users` (admin only)
