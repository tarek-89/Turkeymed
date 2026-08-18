# TurkeyMed

A multilingual medical-tourism marketing site for a Turkey-based clinic. Built with **Laravel 12** and **Filament 5**, styled with the custom **Aurora** design system (Tailwind CSS v4). Content — blog posts, services, homepage sections, offices, testimonials — is managed through the Filament admin panel and served in English plus locale-prefixed translations, with SEO essentials (sitemap, RSS feed, dynamic `robots.txt`, WordPress URL redirects) built in.

## Tech stack

| Area | Choice |
|------|--------|
| Language | PHP 8.2 |
| Framework | Laravel 12 |
| Admin | Filament 5 |
| Frontend | Blade + Tailwind CSS v4 (Aurora design system), Vite |
| Database | SQLite (default), MySQL-compatible |
| Media storage | Local disk, or Cloudflare R2 / S3 (Intervention Image pipeline) |
| Testing | PHPUnit 11 |
| Code style | Laravel Pint |

## Requirements

- PHP 8.2+ with common extensions (`mbstring`, `sqlite3`, `gd` or `imagick` for image processing)
- Composer 2
- Node.js 20+ and npm

## Quick start

From the project root:

```bash
composer setup
```

That single command installs PHP and JS dependencies, creates `.env`, generates the app key, runs migrations, and builds frontend assets. It is defined in `composer.json` and runs:

```bash
composer install
cp .env.example .env          # only if .env is missing
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

### Manual setup

If you prefer to run the steps yourself:

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite by default — file is created automatically)
touch database/database.sqlite
php artisan migrate

# 4. Build assets
npm run build
```

## Running locally

The recommended dev workflow runs the PHP server, queue worker, log viewer, and Vite together:

```bash
composer dev
```

This starts, concurrently:

- `php artisan serve` — the app at http://localhost:8000
- `php artisan queue:listen` — the queue worker
- `php artisan pail` — live log tailing
- `npm run dev` — Vite with hot module reload

Prefer to run pieces separately? In two terminals:

```bash
php artisan serve      # terminal 1 — http://localhost:8000
npm run dev            # terminal 2 — Vite dev server
```

> If a frontend change doesn't show up, the Vite dev server isn't running or assets weren't built — run `npm run dev` (development) or `npm run build` (production).

## Admin panel

The Filament admin panel lives at **`/admin`** (e.g. http://localhost:8000/admin).

Create an admin user:

```bash
php artisan make:filament-user
```

Access is gated by the `ADMIN_EMAILS` env variable — a comma-separated allowlist of emails permitted into `/admin`. Leave it empty to allow any existing user.

From the panel you manage all site content: blog posts, services, the homepage (hero, CTA, treatment cards, testimonials, process steps, galleries, videos, stats, patient results), offices, and social links.

## Configuration

Key settings in `.env` (see `.env.example` for the full list):

**Site & contact**

```dotenv
SITE_PHONE=
SITE_WHATSAPP=
SITE_EMAIL=
ADMIN_EMAILS=          # comma-separated allowlist for /admin
```

**SEO & deployment**

```dotenv
SITE_INDEXABLE=true    # false on staging → whole site is noindex,nofollow
HSTS_ENABLED=false     # true only at go-live on a permanent HTTPS domain
GTM_ID=
GOOGLE_SITE_VERIFICATION=
BING_SITE_VERIFICATION=
```

**Media storage (Cloudflare R2 / S3)** — optional; defaults to local disk

```dotenv
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
R2_ENDPOINT=
R2_PUBLIC_URL=
```

### Using MySQL instead of SQLite

Set the connection in `.env` and re-run migrations:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turkeymed
DB_USERNAME=root
DB_PASSWORD=
```

## URL structure & localization

English is the default language and has no URL prefix; other languages are served under a two-letter locale prefix.

```
/blog/{category}/{slug}              English blog post
/{locale}/blog/{category}/{slug}     Localized blog post
/services/{category}/{slug}          English service page
/{locale}/services/{category}/{slug} Localized service page
/about, /contact                     Static pages (also localized)
```

SEO endpoints are generated dynamically: `/sitemap.xml`, `/feed.xml`, and `/robots.txt` (which respects `SITE_INDEXABLE`). Legacy WordPress URLs are 301-redirected via a redirects table (`HandleRedirects` middleware).

## Design system

All UI follows the **Aurora** design system — the single source of truth for visual design. Tokens live in `resources/css/app.css` (`@theme`), with the full visual reference at `resources/design-system/aurora-reference.html`. Reusable Blade components live under `resources/views/components` (`x-layout.*`, `x-ui.*`, `x-home.*`, `x-service.*`, etc.). See `CLAUDE.md` for the complete component catalog and design rules. Never hardcode colors, fonts, or spacing — use the token utilities.

## Testing & code style

```bash
composer test          # clears config, then runs the PHPUnit suite
php artisan test        # run tests directly
vendor/bin/pint         # auto-fix code style (Laravel Pint)
```

## Building for production

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

At go-live, set `SITE_INDEXABLE=true` and `HSTS_ENABLED=true` on the live domain (keep both off on staging).
