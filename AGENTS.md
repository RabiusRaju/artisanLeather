# AGENTS.md

## Project Identity

This repository is **Artisan Leather**, a premium leather goods commerce and business administration platform based in Muscat, Oman.

Read `PROJECT_HANDOFF.md` before substantial work and `DEPLOYMENT.md` before release or infrastructure changes.

## Repository Boundaries

- `backend/`: Laravel 13 API, Filament 4 admin, database, queues, schedules, prerender, sitemap, and robots.
- `frontend/`: React 19/Vite storefront.
- `.github/workflows/`: independent frontend/backend production deployments.
- Run backend Composer/Artisan commands from `backend/`, not the repository root.
- Run storefront npm commands from `frontend/`.

## Working Rules

- Preserve existing user changes and keep edits narrowly scoped to the request.
- Do not commit, push, create branches, modify production, or run seeders unless explicitly requested.
- Never add secrets, credentials, private keys, customer data, database dumps, or production `.env` files.
- Use `apply_patch` for manual edits.
- Prefer `rg`/`rg --files` for discovery.
- Follow existing Laravel, Filament, React, and Tailwind patterns before introducing abstractions.
- Do not add dependencies unless the task clearly requires them.
- Do not modify an already-applied migration; create a new migration.
- Do not fix unrelated bugs or reformat unrelated files.

## Product and Content Contract

- Public content is admin-managed. If a field is empty, hide the related UI when practical; do not introduce English hard-coded fallback copy without approval.
- English is default; Arabic requires localized fields and RTL-safe presentation.
- Blog posts additionally contain Bangla fields. Do not assume Bangla is supported across every storefront module.
- For new localized fields, update migration, model, Filament form, API output, frontend rendering, validation, and SEO/prerender consumers.
- Product relationships include images, colors, details, specifications, FAQs, reviews, stock, category, and optional brand.
- Product/post JSON import must remain review-first. Images stay manually uploaded unless specifically redesigned.
- Preserve editable automatic ordering/SKU behavior in admin repeaters and product forms.

## Design Contract

- Maintain the established premium black/espresso and antique-gold identity.
- Preserve serif display typography, restrained motion, generous spacing, and minimalist composition.
- Use subtle corner radii only; avoid large casual rounded cards unless explicitly approved.
- Collection grids use three columns on desktop. Product images use `object-contain` where the full item must remain visible.
- Avoid bright marketplace styling, intrusive banners, excessive badges, and crowded controls.
- Do not redesign unrelated sections while implementing a focused request.
- Ensure desktop, tablet, mobile, English, and Arabic layouts remain usable.

## Backend Contract

- Public API routes belong under `/api/v1` in `backend/routes/api.php`.
- Preserve throttling on public write/auth/tracking endpoints.
- Protected customer operations use `auth:sanctum`.
- Keep validation in controllers/form requests and authorization explicit.
- Use Eloquent relationships and casts consistently with existing models.
- Settings should use `Setting::get`, `Setting::set`, or established settings pages so cache invalidation is preserved.
- Uploaded public files use the Laravel public disk and must continue working through `storage:link`.
- If a change affects crawler-visible content, update `PrerenderController`, sitemap/robots behavior, metadata, and structured data as applicable.

## Frontend Contract

- Keep API calls centralized in `frontend/src/services/api.js` and domain hooks.
- Keep route-level composition in `frontend/src/pages/`; reusable sections belong in `components/`.
- Preserve top-level providers and localStorage keys unless a migration strategy is included.
- Use the existing i18next language flow and `Accept-Language` header.
- Preserve accessible names, keyboard behavior, heading hierarchy, alt text, contrast, focus visibility, and reduced layout shift.
- Lazy-load non-home route pages and below-the-fold media where appropriate.
- Do not expose private/admin fields in public API responses.

## Database and Deployment Safety

- Before adding a migration, inspect the current table and migration history.
- Never drop or recreate a production table to resolve a migration mismatch without explicit approval and a recovery plan.
- Do not run `php artisan db:seed` in production by default.
- Backend deployment automatically runs `php artisan migrate --force`; code must be backward-aware during rollout where practical.
- Frontend API URL is embedded at build time through `VITE_API_BASE_URL`.
- Queue workers are required for queued order email; scheduler is required for analytics and news jobs.
- Do not change Caddy/prerender assumptions without updating deployment documentation.

## Validation Expectations

Start with checks closest to the changed code, then broaden when risk warrants it.

Backend:

```bash
cd backend
php -l path/to/changed-file.php
php artisan test
./vendor/bin/pint --test
```

Frontend:

```bash
cd frontend
npm run lint
npm run build
```

Current meaningful automated coverage is limited. For customer-facing behavior, perform focused manual checks and state exactly what was and was not verified.

For schema/API/frontend changes, verify:

- create and edit admin flows;
- English and Arabic output;
- API response shape;
- storefront desktop/mobile rendering;
- prerender/SEO output when relevant;
- production migration requirements.

## Completion Report

At handoff, state:

- what changed and why;
- clickable file references with line numbers;
- migrations or environment changes required;
- commands/tests run and their results;
- anything not verified or any remaining production risk.

