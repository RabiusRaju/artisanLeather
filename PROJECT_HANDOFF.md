# Artisan Leather Project Handoff

Last reviewed: 2 November 2026

## 1. Purpose

This document transfers the business, product, technical, operational, and deployment knowledge needed to continue the Artisan Leather platform safely.

Artisan Leather is a premium leather goods brand based in Muscat, Oman. The platform supports a public e-commerce storefront, a multilingual content and SEO system, customer accounts, order operations, and a broad Laravel Filament back office.

This is an implementation handoff for the existing application. It is not a replacement for legal, finance, security, or infrastructure policies.

## 2. Production Services

| Service | URL | Responsibility |
| --- | --- | --- |
| Storefront | `https://artisanleatherom.com` | React customer-facing website |
| Storefront alias | `https://www.artisanleatherom.com` | Redirect/alias for the storefront |
| API and admin | `https://api.artisanleatherom.com` | Laravel API, Filament admin, sitemap, robots, and prerender output |
| Admin panel | `https://api.artisanleatherom.com/admin` | Authorized business administration |
| Public API | `https://api.artisanleatherom.com/api/v1` | Storefront data and commerce endpoints |

Never store production passwords, API keys, private SSH keys, customer data, or payment information in this repository or this document.

## 3. Business Scope

### Customer-facing capabilities

- Product collections, categories, brands, product details, colors, specifications, FAQs, tags, videos, and image galleries.
- Dynamic product CTA behavior such as Add to Cart, Pre-order, Sold Out, and Enquire.
- Cart, checkout, order confirmation, customer registration/login, account order history, order tracking, wishlist, reviews, and coupons.
- Homepage, About page, Contact page, legal pages, testimonials, global FAQs, and configurable business settings.
- Blog/Leather Journal with English, Arabic, and Bangla content fields.
- Product share links with language selection and UTM/share tracking.
- Newsletter subscription and lead capture.
- Surveys, including a staff collection mode controlled by an admin-configured PIN.
- English/Arabic storefront localization and right-to-left layout for Arabic.
- Dynamic SEO metadata, structured data, sitemap, robots rules, social previews, and crawler prerendering.
- GA4, Google Tag Manager/dataLayer, Meta-style tracking hooks, UTM capture, and analytics synchronization.

### Back-office capabilities

- Products, categories, brands, inventory, stock movements, reviews, coupons, orders, customers, custom orders, contact messages, and newsletter subscribers.
- Homepage, About page, legal content, business settings, currencies, FAQs, testimonials, blog posts, news staging, and news sources.
- Product/post JSON import and export for assisted content workflows. Image files remain manually managed.
- Sales, product, customer, seasonal, web, GA4, and Search Console analytics.
- Finance and compliance modules: expenses, income, purchase orders, suppliers, accounts payable/receivable, cash flow, VAT, budgets, bank reconciliation, profit/loss, and balance sheet.
- HR and operations modules: departments, employees, craftsmen, production tasks, timesheets, inventory, and master location data.
- Activity logging for important content such as products and posts.

## 4. Technology Stack

### Backend

- PHP `^8.3`; CI currently builds with PHP 8.4.
- Laravel 13.
- Filament 4 admin panel.
- Laravel Sanctum token authentication.
- MySQL in production; `.env.example` defaults to SQLite for local bootstrapping.
- Database-backed queues, cache, and sessions by default.
- Intervention Image for image handling.
- Spatie Activity Log.
- Google Analytics Data and Google API clients.
- OpenAI and Anthropic SDKs for optional admin content generation.

### Frontend

- React 19.
- Vite 8.
- React Router 7.
- Tailwind CSS 3 for the standalone storefront.
- Axios, i18next, Framer Motion, React Helmet Async, and React Icons.
- Static SPA output deployed from `frontend/dist`.

### Infrastructure

- Hostinger Ubuntu VPS.
- Caddy for HTTPS, static storefront delivery, PHP-FPM routing, SPA fallback, and crawler routing.
- GitHub Actions deploy frontend and backend independently on pushes to `main`.
- Production paths currently documented as `/var/www/artisanleather/frontend` and `/var/www/artisanleather/backend`.

See `DEPLOYMENT.md` and `.github/workflows/` for the current deployment implementation.

## 5. Repository Structure

```text
artisan_leather/
├── backend/                 Laravel API and Filament admin
│   ├── app/Filament/        Admin pages, resources, widgets
│   ├── app/Http/Controllers API and prerender controllers
│   ├── app/Models/          Domain models
│   ├── app/Jobs/            Analytics and news synchronization
│   ├── database/migrations/ Database history
│   ├── database/seeders/    Optional initial/sample data
│   ├── routes/api.php       Versioned public/protected API
│   ├── routes/web.php       Admin support, prerender, sitemap, robots
│   └── routes/console.php   Scheduled jobs
├── frontend/                Public React storefront
│   ├── src/components/      Shared visual and behavioral components
│   ├── src/context/         Auth, cart, currency, theme, toast, wishlist
│   ├── src/hooks/           API-facing React hooks
│   ├── src/pages/           Route-level screens
│   ├── src/services/api.js  Axios API contract
│   └── src/i18n/            English and Arabic translations
├── .github/workflows/       Production deployment workflows
├── DEPLOYMENT.md            VPS and GitHub Actions deployment notes
├── PROJECT_HANDOFF.md       This document
└── AGENTS.md                Instructions for coding agents
```

The root `composer.json` is not the Laravel application manifest. Backend PHP work should normally run from `backend/` using `backend/composer.json`.

## 6. Application Architecture

### Request flow

```text
Browser
  -> artisanleatherom.com (Caddy serves React SPA)
  -> React calls api.artisanleatherom.com/api/v1
  -> Laravel controllers validate and query Eloquent models
  -> JSON response returns to React contexts/hooks/pages

Search/social crawler
  -> Caddy identifies supported crawler request
  -> api.artisanleatherom.com/prerender/*
  -> Laravel returns metadata-rich HTML
```

The public site and API are separate deployments. Changing `VITE_API_BASE_URL` requires rebuilding the frontend because Vite embeds it at build time.

### Backend boundaries

- `routes/api.php` is the public storefront contract. Public routes are generally limited to 120 requests/minute, with stricter limits for checkout, contact, login, newsletter, tracking, and coupon validation.
- Protected customer routes use Sanctum bearer tokens.
- `PrerenderController` produces crawler/social HTML for home, collections, products, blog, surveys, sharing, About, Contact, tracking, and returns pages.
- `routes/web.php` generates `sitemap.xml` and `robots.txt` dynamically.
- Filament resources are the primary business administration layer.
- Settings are database-backed through `Setting`; values are cached for one hour and invalidated when saved/deleted.

### Frontend boundaries

- `App.jsx` owns routes and top-level providers.
- `services/api.js` owns the Axios client, language header, auth token, and survey staff PIN headers.
- Context providers own cross-page state: authentication, cart, wishlist, currency, theme, settings, toast.
- Route pages own page composition; reusable site sections live under `components/`.
- SEO tags are rendered client-side through `SEO`/Helmet, supplemented by Laravel prerender output for crawlers.

## 7. Public Routes

The storefront includes:

- `/`
- `/collections` and `/collections/:category`
- `/product/:slug`
- `/blog` and `/blog/:slug`
- `/share/:token`
- `/survey/:slug`
- `/about`, `/contact`
- `/cart`, `/checkout`, `/order-confirmation`
- `/login`, `/register`, `/account`, `/wishlist`
- `/track` and `/track/:orderNumber`
- `/privacy`, `/terms`, `/returns`

Private/transactional routes are intentionally disallowed in `robots.txt`.

## 8. API Summary

Base path: `/api/v1`

### Public read operations

- Products: list and detail by slug.
- Categories, currencies, brands, posts, settings, testimonials, FAQs, legal pages.
- Product reviews, featured coupons, surveys, share links, and order tracking.

### Public write operations

- Place an order.
- Submit contact and newsletter forms.
- Validate coupons.
- Submit surveys.
- Register and log in.

### Authenticated operations

- Logout, current user, order history, last order.
- Submit product reviews.
- Read, sync, and toggle wishlist.

When adding or changing API fields, update all of the following together:

1. Migration/model/casts or relationships.
2. Filament create/edit fields where business users manage the value.
3. Controller/resource JSON response.
4. Frontend hook/service and consuming page/component.
5. Prerender/structured data if the field affects SEO or social sharing.
6. English/Arabic behavior and validation.

## 9. Important Domain Rules

### Products

- Products belong to a category and may belong to a brand/collection.
- Related records include images, colors, bullet details, specifications, FAQs, reviews, and inventory stock.
- Product image order controls gallery/main image order.
- Product display order controls collection ordering; lower values appear first.
- Product SKUs follow an editable auto-generated pattern with a four-digit suffix.
- Product JSON import/export can update all or selected tabs, but images are uploaded manually.
- Product images currently allow up to 12 items in the Filament form.
- Collection cards use three columns on desktop and `object-contain` to avoid image cropping.
- The luxury visual language is black/espresso, antique gold, ivory text, serif display typography, restrained motion, and subtle corner radii.

### Content and localization

- English is the default storefront language.
- Arabic uses `?lang=ar`, the `Accept-Language` header, localized API fields, and RTL document direction.
- Blog posts also contain Bangla fields; confirm frontend presentation before expanding Bangla beyond blog/content workflows.
- Empty admin content should remain empty/hidden on the frontend. Do not reintroduce hard-coded copy as silent fallback unless explicitly approved.
- Homepage and About page labels, images, SEO, CTA labels, and CTA URLs are intended to be admin-managed.

### SEO

- Keep one canonical URL per page and localized alternate metadata where implemented.
- Products and posts have localized SEO fields.
- Public images should have admin-managed alt text; decorative images should use empty alt text.
- Preserve Product, Breadcrumb, Organization, FAQ, Review, and article structured data where applicable.
- Keep the dynamic sitemap and prerender routes aligned with new public pages.
- Do not expose cart, checkout, account, login, registration, confirmation, admin, or API internals to indexing.

### Orders and background work

- Order placement is rate-limited and queues customer/admin confirmation mail.
- A persistent queue worker is required in production when `QUEUE_CONNECTION=database`.
- Scheduled jobs sync GA4 hourly, Search Console daily at 03:00, and news feeds every six hours.
- A cron entry must execute `php artisan schedule:run` every minute.

## 10. Local Development

### Requirements

- PHP 8.3 or newer with Laravel-required extensions.
- Composer 2.
- Node.js 22 and npm.
- MySQL for production parity, or SQLite for lightweight local setup.

### Backend setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve --port=8000
```

Configure the database before migration. Do not run seeders unless sample/initial data is explicitly wanted.

For queues and scheduled work during development:

```bash
cd backend
php artisan queue:work
php artisan schedule:work
```

### Frontend setup

```bash
cd frontend
npm ci
cp .env.example .env
npm run dev
```

Set:

```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

If the frontend or backend port changes, update the frontend environment and allowed CORS origins accordingly.

## 11. Environment Configuration

Important backend variables include:

- `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`
- `FILESYSTEM_DISK`
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- `OPENAI_API_KEY`, `ANTHROPIC_API_KEY` for optional AI content tools
- `GOOGLE_APPLICATION_CREDENTIALS`, `GA4_PROPERTY_ID`, `SEARCH_CONSOLE_SITE_URL`

Important frontend variables:

- `VITE_API_BASE_URL`

Production requirements:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://api.artisanleatherom.com`
- Real MySQL, mail, cache, queue, session, storage, and analytics configuration.
- Never commit `.env` files or credentials JSON.

## 12. Database and Migrations

- Treat migrations as immutable after they have run in production. Add a new migration for schema changes.
- Before deployment, review `php artisan migrate:status` and back up the database.
- Deploy with `php artisan migrate --force` only from the actual backend directory containing `artisan`.
- If a table exists but its migration is not recorded, stop and reconcile the schema/migrations table before continuing. Do not delete a production table just to satisfy a migration.
- Do not run `php artisan db:seed` in production unless the exact seeder and expected effects have been reviewed. Several seeders contain sample or initial content.
- Product/content additions generally require migrations plus admin/API/frontend updates; deploying code without its migration can cause 500 errors.

## 13. Media and Storage

- Public product/content media is stored through Laravel's public disk and requires `php artisan storage:link`.
- CI intentionally excludes `storage/app/public` so deployments do not delete uploaded media.
- Back up uploaded media separately from the Git repository.
- Product upload validation accepts JPG, PNG, and WebP with a 5 MB per-file limit; product images are converted/resized by the application workflow.
- Use consistent primary product images for collection grids. Keep diagrams, dimension graphics, and lifestyle banners as secondary gallery images where possible.
- Public images should have useful, concise alt text in the relevant language.

## 14. Deployment

Frontend and backend deploy independently from `main`:

- Frontend workflow: installs Node dependencies, builds with the production API base URL, and rsyncs `dist/`.
- Backend workflow: installs production Composer dependencies, rsyncs the Laravel app, runs migrations, caches config/routes/views, optimizes Filament, links storage, and restarts queues.

Required GitHub Actions secrets are documented in `DEPLOYMENT.md`. Never paste secret values into issues, documentation, commits, or chat transcripts.

### Deployment checklist

1. Review the diff and identify migrations, environment changes, queue jobs, and frontend API contract changes.
2. Back up the production database and uploaded media for material releases.
3. Run backend tests and frontend lint/build locally.
4. Confirm any new environment variables exist in production before code is activated.
5. Push to `main` or manually dispatch the relevant workflow.
6. Watch both GitHub Actions jobs to completion.
7. Confirm migrations completed and the queue worker restarted.
8. Run the smoke tests below.
9. Check Laravel logs and browser console/network errors.

### Production smoke tests

- Storefront home, collections, product, blog, About, Contact, and legal pages load.
- English/Arabic switch works and Arabic layout is RTL.
- Product image gallery, CTA, currency, cart, checkout, and coupon validation work.
- Test order can be created and tracked; confirmation mail is processed by the queue.
- Login, account order history, wishlist, and review submission work.
- Admin login works and product/post edit forms load.
- Uploaded images resolve from `/storage`.
- `sitemap.xml`, `robots.txt`, and representative prerender endpoints return successfully.
- Newsletter/contact forms work and rate limiting does not block normal use.
- GA/GTM events and UTM attribution remain present where expected.

## 15. Testing and Quality Status

Current automated test coverage is minimal: the repository contains Laravel's example unit and feature tests, with no meaningful storefront end-to-end suite found during this handoff review.

Before relying on automated deployment as a release gate, add focused tests for:

- Product/category/post/settings API localization.
- Order creation, pricing, coupons, UTM attribution, and inventory effects.
- Authentication, wishlist, review authorization, and rate limits.
- Product/admin form relationships and JSON import validation.
- Sitemap, robots, prerender metadata, and structured data.
- Frontend collection, product, cart, checkout, language, and account flows.

Useful current checks:

```bash
cd backend
php artisan test
./vendor/bin/pint --test

cd ../frontend
npm run lint
npm run build
```

Do not fix unrelated failures silently. Report them separately from the requested change.

## 16. Backups and Recovery

Back up at least:

- MySQL database.
- `backend/storage/app/public` uploads.
- Production `.env` and Google credentials using a secure secret store.
- Caddy configuration and service definitions.
- GitHub Actions secret inventory (names/owners, never plaintext values).

Rollback strategy:

- Frontend: redeploy a known-good commit.
- Backend code: redeploy a known-good commit, but assess database compatibility first.
- Database: prefer forward-fix migrations. Restore a backup only when the data impact and downtime are understood.
- Media: restore the upload backup without overwriting newer valid files unless intentionally rolling back.

## 17. Security and Privacy Notes

- Admin access must remain authenticated and least-privileged.
- Production currently documents SSH deployment using the root account. Moving deployment to a restricted service user is recommended.
- Keep `APP_DEBUG=false` in production.
- Do not log secrets, tokens, passwords, customer private data, or payment details.
- Preserve API throttling and authorization checks when changing routes.
- Validate uploads by content/type/size, not filename alone.
- Review CORS when adding domains; do not use unrestricted origins with credentialed requests.
- Rotate credentials when team ownership changes.
- Review privacy/cookie consent and marketing tracking against applicable Oman, GCC, UK, and US requirements with qualified legal counsel.

## 18. Known Risks and Follow-up Work

1. Automated coverage is too small for the feature breadth; checkout and content regressions currently depend heavily on manual testing.
2. Queue and scheduler processes are operational dependencies and must be monitored separately from successful web deployment.
3. The split SPA/prerender architecture requires Caddy crawler routing to stay synchronized with public routes.
4. Database-backed settings cache for up to one hour; writes through `Setting::set` invalidate individual keys, but direct database changes may appear stale until cache expiry/clear.
5. Multilingual behavior is not uniform across every domain: storefront is primarily English/Arabic, while blog data also includes Bangla.
6. The admin system contains many business modules. Changes should remain scoped; storefront requests should not trigger unrelated finance/HR refactors.
7. The deployment workflow uses destructive rsync (`--delete`) while excluding runtime files. Review exclusions before adding new server-owned directories.
8. The existing deployment documentation contains infrastructure identifiers. Keep future public copies sanitized.

## 19. Ownership Handoff Checklist

The outgoing owner should transfer securely, outside Git:

- Hostinger/VPS ownership and SSH access.
- Domain/DNS ownership.
- GitHub repository administration and Actions secrets ownership.
- Production database access and backup procedure.
- Mail provider credentials and sender-domain configuration.
- Google Analytics, Tag Manager, Search Console, and service-account ownership.
- OpenAI/Anthropic account ownership if AI tools remain enabled.
- Meta/advertising integrations and consent configuration.
- Admin user list and role expectations.
- Incident contact, release approver, content owner, finance owner, and privacy owner.

## 20. Definition of a Safe Change

A change is ready to hand over when:

- It follows the existing black/gold premium design and multilingual rules.
- Admin-managed content remains dynamic rather than silently hard-coded.
- Backend, API, frontend, prerender, and structured data are updated together where applicable.
- Migrations are additive and deployable against production data.
- Focused validation and build checks pass.
- Production environment and operational requirements are documented.
- The release can be verified using a short, explicit smoke-test list.

