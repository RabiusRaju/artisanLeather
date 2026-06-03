<x-filament-panels::page>

<div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 mb-6 flex items-start gap-3">
    <span class="text-2xl mt-0.5">🚀</span>
    <div>
        <h3 class="font-bold text-amber-800 dark:text-amber-300">Deployment Checklist — artisanleatherom.com</h3>
        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Follow these steps in order to go live. Estimated time: 2–4 hours with a server admin.</p>
    </div>
</div>

@php
$phases = [
    ['icon'=>'🖥️','title'=>'Phase 1 — Server Setup','steps'=>[
        ['done'=>false,'task'=>'Get a VPS (Virtual Private Server)','detail'=>'Recommended: DigitalOcean, Hetzner, or Vultr. Min spec: 2GB RAM, 2 CPU, 50GB SSD. Choose Frankfurt or Amsterdam region for GCC latency.'],
        ['done'=>false,'task'=>'Install Ubuntu 22.04 LTS + LEMP stack','detail'=>'Install Nginx, MySQL 8, PHP 8.4, Composer, Node.js 20. Use a server setup script like Cleavr or Ploi for easy setup.'],
        ['done'=>false,'task'=>'Configure firewall (UFW)','detail'=>'Allow ports: 22 (SSH), 80 (HTTP), 443 (HTTPS). Block all others.'],
        ['done'=>false,'task'=>'Create database','detail'=>'CREATE DATABASE artisan_leather CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; Create a dedicated DB user — never use root.'],
    ]],
    ['icon'=>'🌐','title'=>'Phase 2 — Domain & SSL','steps'=>[
        ['done'=>false,'task'=>'Point artisanleatherom.com DNS to server','detail'=>'Add A record: artisanleatherom.com → [Your Server IP]. Add A record: www.artisanleatherom.com → [Your Server IP]. Wait 30min for propagation.'],
        ['done'=>false,'task'=>'Install SSL certificate (free via Let\'s Encrypt)','detail'=>'sudo apt install certbot python3-certbot-nginx -y && sudo certbot --nginx -d artisanleatherom.com -d www.artisanleatherom.com. Auto-renews every 90 days.'],
    ]],
    ['icon'=>'📦','title'=>'Phase 3 — Deploy Backend (Laravel)','steps'=>[
        ['done'=>false,'task'=>'Clone backend to /var/www/artisan-leather-backend','detail'=>'git clone [your-repo] /var/www/artisan-leather-backend && cd /var/www/artisan-leather-backend && composer install --no-dev --optimize-autoloader'],
        ['done'=>false,'task'=>'Set production .env','detail'=>'Copy .env.example to .env. Set: APP_ENV=production, APP_DEBUG=false, APP_URL=https://artisanleatherom.com, DB_*, MAIL_MAILER=smtp (configure your SMTP).'],
        ['done'=>false,'task'=>'Run Laravel production commands','detail'=>'php artisan key:generate && php artisan migrate --force && php artisan db:seed && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize'],
        ['done'=>false,'task'=>'Set correct file permissions','detail'=>'chown -R www-data:www-data /var/www/artisan-leather-backend && chmod -R 755 storage bootstrap/cache'],
        ['done'=>false,'task'=>'Configure Nginx for backend API','detail'=>'Set up Nginx to serve Laravel at api.artisanleatherom.com or artisanleatherom.com/api. Point to /public/index.php.'],
    ]],
    ['icon'=>'⚛️','title'=>'Phase 4 — Deploy Frontend (React)','steps'=>[
        ['done'=>false,'task'=>'Update React API base URL','detail'=>'In frontend/src/services/api.js change baseURL from http://localhost:8000/api/v1 to https://artisanleatherom.com/api/v1 (or your API URL).'],
        ['done'=>false,'task'=>'Build React for production','detail'=>'cd frontend && npm run build. This creates a dist/ folder with optimised static files.'],
        ['done'=>false,'task'=>'Deploy dist/ to Nginx or CDN','detail'=>'Copy dist/ contents to /var/www/artisan-leather-frontend. Configure Nginx to serve index.html for all routes (SPA config). Or deploy to Cloudflare Pages / Vercel (free).'],
    ]],
    ['icon'=>'✅','title'=>'Phase 5 — Final Verification','steps'=>[
        ['done'=>false,'task'=>'Test the website end-to-end','detail'=>'Open artisanleatherom.com. Browse products, add to cart, complete a test order. Verify email is received. Check admin at artisanleatherom.com/admin.'],
        ['done'=>false,'task'=>'Configure Oman phone numbers in code','detail'=>'Search codebase for +968 1234 5678 (placeholder) and replace with real business phone number throughout.'],
        ['done'=>false,'task'=>'Set up error monitoring','detail'=>'Install Sentry (free tier) for Laravel + React to catch errors in production. composer require sentry/sentry-laravel.'],
        ['done'=>false,'task'=>'Set up automated backups','detail'=>'Configure daily DB backup: mysqldump artisan_leather | gzip > backup_$(date +%Y%m%d).sql.gz. Store in DigitalOcean Spaces or AWS S3.'],
        ['done'=>false,'task'=>'Configure production email (SMTP)','detail'=>'Set up Mailgun, SendGrid, or AWS SES. Update .env MAIL_* variables. Test order confirmation email works.'],
    ]],
];
@endphp

@foreach($phases as $phase)
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm mb-4">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
        <span class="text-xl">{{ $phase['icon'] }}</span>
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $phase['title'] }}</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($phase['steps'] as $i => $step)
        <div class="px-5 py-4 flex gap-4 items-start">
            <div class="mt-0.5 w-6 h-6 rounded-full border-2 {{ $step['done']?'bg-green-500 border-green-500':'border-gray-300 dark:border-gray-600' }} flex items-center justify-center flex-shrink-0">
                @if($step['done'])<span class="text-white text-xs">✓</span>@endif
            </div>
            <div class="flex-1">
                <p class="font-medium text-sm text-gray-900 dark:text-white">{{ $step['task'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $step['detail'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

<div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5 text-center">
    <p class="text-2xl mb-2">🎉</p>
    <h3 class="font-bold text-green-800 dark:text-green-300 text-lg">Your business is ready to go live!</h3>
    <p class="text-sm text-green-700 dark:text-green-400 mt-1">artisanleatherom.com will be live and taking real orders from customers across Oman and the GCC.</p>
    <p class="text-xs text-gray-500 mt-3">Need help with deployment? Share this checklist with your server administrator.</p>
</div>
</x-filament-panels::page>
