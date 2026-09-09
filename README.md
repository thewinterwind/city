# City — Bacolod launch

A Laravel 13.31.0 city discovery directory. The first deployment serves https://bacolod.com. Every future city uses this same application, database schema, category taxonomy, templates and features. The request hostname selects an active `cities` record; unknown hosts are rejected. Other existing city domains have not been activated in this application.

## Included in this free launch

- Database-driven category navigation and horizontal homepage rows, four photo cards visible on desktop, mobile swipe, browse-all pages and listing details.
- 35 researched places across Bacolod and clearly labelled nearby Talisay: five in each of seven shared categories. No category starts with more than ten records.
- Real venue photographs, area labels, concise descriptions, directions and source links. Image provenance is recorded in `database/data/photo-sources.json`; public credits appear on listing details. No invented reviews, prices or hours.
- Local-browser saved places, with shareable shortlist URLs that reveal only listing IDs.
- Business registration, sign-in, password changes, free listing submissions, photo uploads, ownership claims and corrections.
- Administrator review queue, listing edits and archival. Claims require an explicit independent-verification checkbox. Public places cannot be claimed. New listings and edits remain pending until reviewed. Existing photos are retained when an edit has no new photo.
- No payment, affiliate, booking, advertising, public-review or news integrations.

## Runtime and database

PHP 8.4.25 on the launch server, with dependencies locked in `composer.lock`. Laravel itself requires PHP 8.3 or newer; this locked dependency set requires PHP 8.4.1 or newer. Use PHP 8.4 for this checkout. The Docker runtime is independent of existing sites' PHP installations.

SQLite is the initial authoritative database, stored on the server's persistent volume at `/var/www/city/database/city.sqlite`, outside the container image and public web root. WAL and a busy timeout are configured. The schema also supports MySQL for a later database migration. All cities share one database, with `city_id` on listings, submissions and audit records. Categories and user identities are shared; sessions are host-only.

Tables: `cities`, `categories`, `users`, `listings`, `submissions`, `audit_events`, `migrations`.

Seeding uses `firstOrCreate`, so it does not overwrite owner/admin edits on subsequent deployments. Initial sources and research dates live in `database/data/bacolod.json`. `CuratedCardsSeeder` is a one-time photo/rank backfill for existing launch listings; it skips already initialized photos and preserves live owner uploads and existing ranks. Do not rerun it as a routine deployment step. The initial facts do not imply current hours, availability or an endorsement. The public plaza entry explicitly mentions reported redevelopment.

## Local setup

```sh
cp .env.example .env
composer install
php artisan key:generate
# Set DB_DATABASE in .env to an absolute writable SQLite path.
php artisan migrate
php artisan db:seed
php artisan serve
```

For container-based development, build `deploy/Dockerfile` and mount the checkout at `/app`. Create writable directories under `storage/framework/{cache/data,sessions,views}`, `storage/logs`, `storage/app/public` and `bootstrap/cache`. Host `localhost` resolves to Bacolod only in local/testing environments.

```sh
composer test
```

The automated suite uses an isolated in-memory database, never the production database. The isolated tests cover: real seed limits/idempotency, public routes and filters, city isolation, unknown hosts, auth, claims, edit field preservation, harmful URLs, cross-city submission IDs, non-admin registration, and unpublished shortlist protection.

## Administration

There is no default or shared administrator password. Register your own account on the site, then an authorised server operator grants the role:

```sh
docker exec city-php php artisan city:admin your-email@example.com
```

The email must already exist. Use `/admin` to review requests and `/dashboard` to manage listings. Verify claims through the business's independently sourced official contact channel before approving. Claim evidence and account emails are never public. Public place edits remain available to administrators. Public listing corrections must be applied through a reviewed edit before the correction itself is marked approved.

Password reset email, deletion automation and email notifications are not configured in this first launch. Requests are tracked in the dashboard. Account recovery/removal currently needs the site administrator. Set up authenticated mail before adding recovery emails; do not enable a public unauthenticated reset mechanism.

## Add a city later

1. Add a `cities` row with its exact root domain, slug, name, tagline, intro and active flag.
2. Add researched `listings` rows with that `city_id` and the existing category IDs. Category rows use optional `editorial_rank` (1 first, null last) with stable name/ID tie-breaking. Admins can reorder each category in `/admin`; ranks are city scoped and audited. Owner edits cannot set ranks. `featured_position` is a legacy field.
3. Route the new domain through the same application origin. Keep Host forwarding, all required HTTP methods, cookies and query strings enabled, with dynamic caching disabled.
4. Set up that domain's TLS and canonical www redirect, verify city isolation, and launch.

Do not fork templates or copy the application for the next city.

## Existing AWS deployment

- EC2: `i-0415c2c12d36bdf17`, `ap-southeast-1`.
- Application: `/var/www/city`, container `city-php`, PHP-FPM bound to loopback port 9084.
- Nginx: `/etc/nginx/conf.d/bacolod-city.conf`, based on `deploy/nginx.conf`.
- CloudFront: `E20Z5MITP8DFJ6` for Bacolod and www. Existing disabled-cache/all-viewer policies and seven allowed methods are retained.
- Origin requests require the private `CITY_ORIGIN_TOKEN`; never commit it. The shared domain-sales CloudFront origin header is validated using constant-time comparison. The appended CloudFront viewer address is used for request limits.
- Only Bacolod was removed from the existing domain-sales Nginx virtual host. That original configuration is retained at `/var/backups/city-launch/domain-sales-origin.conf`.
- The application mount is read-only at runtime; storage, cache and database directories are writable. Runtime process/memory/log limits are configured.

Use `composer install` with the committed lock file for deployments. Run migrations explicitly, seed only when adding initial data, then clear/rebuild views. Do not replace `.env`, the database or uploaded photos when deploying source updates. `deploy/run-container.sh` is for initial container creation; do not run it blindly against an existing named container.

A local backup script uses SQLite's online backup API and preserves uploaded photos. Its daily schedule is installed at `/etc/cron.d/city-backup`. Local copies protect against an application mistake; off-server backup should be configured before substantial operator onboarding.

## Source repository

The shared source repository is https://github.com/thewinterwind/city. Commit application changes and the dependency lock file here. The live website database remains authoritative for listings, accounts and submissions. Do not commit production `.env`, credentials, database files, private submissions, uploaded business photos or logs.

## Homepage photo-row update

Deploy source and public photo assets, run `php artisan migrate --force`, then `php artisan db:seed --force` to add only missing initial venues. For the original launch database only, run `php artisan db:seed --class=CuratedCardsSeeder --force` once to initialize existing listings' card metadata. Refresh compiled views afterwards. The migration adds fields without replacing listing IDs, users, submissions, uploads or the database.

Homepage ordering is an editorial choice maintained per city/category; it is not represented as a public review score. Public places remain unclaimable. The top four appear at desktop widths; arrows or horizontal swiping reveal further places. The home carousel is capped at ten per category, while See all uses the complete published category with pagination.
