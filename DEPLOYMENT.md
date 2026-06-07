# Deploying Artisan Leather to Hostinger VPS via GitHub Actions

## Server setup (already done)

- **VPS**: Hostinger KVM 1, Ubuntu 24.04, IP `69.62.78.34`
- **Stack installed**: PHP 8.3 (+ extensions), MySQL, Composer, Node.js 22, Caddy
- **Database**: `artisan_leather` / user `artisan_user` (MySQL, localhost)
- **App directories**:
  - `/var/www/artisanleather/backend` — Laravel app (rsynced by CI)
  - `/var/www/artisanleather/frontend` — React static build (rsynced by CI)
- **`.env`**: created manually at `/var/www/artisanleather/backend/.env` (never touched by CI —
  `--exclude='.env'` in the rsync step keeps secrets off GitHub)
- **Caddy**: configured at `/etc/caddy/Caddyfile`, automatic HTTPS via Let's Encrypt for:
  - `artisanleatherom.com` / `www.artisanleatherom.com` → serves `frontend/` (SPA fallback to `index.html`)
  - `api.artisanleatherom.com` → serves `backend/public` via PHP-FPM
- **DNS**: A records for `@`, `www`, `api` all point to `69.62.78.34`
- **Deploy SSH key**: dedicated ed25519 keypair (`artisan_deploy_key`) added to `root`'s
  `~/.ssh/authorized_keys`, used only by GitHub Actions (separate from your personal access —
  revocable independently)

## Two GitHub Actions workflows

- `.github/workflows/deploy-frontend.yml` — builds the React app (`npm run build` with
  `VITE_API_BASE_URL` baked in), rsyncs `frontend/dist/` over SSH to `/var/www/artisanleather/frontend`
- `.github/workflows/deploy-backend.yml` — runs `composer install --no-dev`, rsyncs the Laravel
  app over SSH to `/var/www/artisanleather/backend` (excluding `.env` and runtime storage dirs),
  then SSHes in to run `migrate --force`, `config:cache`, `route:cache`, `view:cache`,
  `filament:optimize`, `storage:link`, `queue:restart`

Both trigger on push to `main` (only when their folder changes) and can be run manually from the
GitHub **Actions** tab (`workflow_dispatch`).

## GitHub repository secrets to add

Go to **GitHub repo → Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Value |
|---|---|
| `VITE_API_BASE_URL` | `https://api.artisanleatherom.com/api/v1` |
| `HOSTINGER_SSH_HOST` | `69.62.78.34` |
| `HOSTINGER_SSH_PORT` | `22` |
| `HOSTINGER_SSH_USERNAME` | `root` |
| `HOSTINGER_SSH_PRIVATE_KEY` | full contents of `artisan_deploy_key` (the **private** key, including `-----BEGIN/END-----` lines) |
| `HOSTINGER_BACKEND_REMOTE_DIR` | `/var/www/artisanleather/backend` |
| `HOSTINGER_FRONTEND_REMOTE_DIR` | `/var/www/artisanleather/frontend` |

## First deploy

1. Add all the secrets above.
2. Push to `main` (or trigger both workflows manually from the **Actions** tab).
3. Watch the run logs:
   - Frontend job: `npm ci` → `npm run build` → rsync `dist/` to the server
   - Backend job: `composer install --no-dev` → rsync app → SSH in and run
     `migrate --force`, cache commands, `storage:link`, `queue:restart`
4. Visit `https://api.artisanleatherom.com/admin` to confirm the Filament panel loads
   (Caddy auto-provisions the SSL cert on first request — may take a few seconds).
5. Visit `https://artisanleatherom.com` to confirm the storefront loads and can reach the
   API (check browser DevTools → Network tab for failed CORS / 404s on `/api/v1/...`).

## Notes

- `config/cors.php` whitelists `https://artisanleatherom.com` and `https://www.artisanleatherom.com`
  — update it if you ever add more frontend domains.
- The frontend reads its API URL from `VITE_API_BASE_URL` at **build time** — baked into the
  static bundle. If you change it, you must re-run the frontend workflow to rebuild.
- `start.sh` / local `Caddyfile` in the repo root are dev-only tooling for your Mac; the VPS has
  its own separate `/etc/caddy/Caddyfile`.
- To add a queue worker that survives reboots, set up a systemd service running
  `php artisan queue:work` in `/var/www/artisanleather/backend` — ask if you want help with this.
- To set up the Laravel scheduler (if any scheduled tasks are added later), add a cron entry:
  `* * * * * cd /var/www/artisanleather/backend && php artisan schedule:run >> /dev/null 2>&1`
