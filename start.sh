#!/bin/zsh
# ── Artisan Leather — Start Development Servers ──────────────────────────────
echo "🚀 Starting Artisan Leather development servers..."

# 1. Stop any existing processes
pkill -f "artisan serve" 2>/dev/null
pkill -f "php-fpm" 2>/dev/null
caddy stop 2>/dev/null
sleep 1

# 2. Start PHP-FPM 8.4 (handles PHP execution)
/opt/homebrew/sbin/php-fpm --daemonize
sleep 1
echo "✅ PHP-FPM 8.4 started (port 9000)"

# 3. Start Caddy (handles HTTP, serves static files, proxies PHP to FPM)
caddy start --config /Applications/XAMPP/xamppfiles/htdocs/artisan_leather/Caddyfile
sleep 2
echo "✅ Caddy started (port 8000)"

# 4. Start Frontend (Vite)
cd /Applications/XAMPP/xamppfiles/htdocs/artisan_leather/frontend
npm run dev &
VITE_PID=$!
echo "✅ Vite frontend started (port 5173)"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Admin Panel  → http://127.0.0.1:8000/admin"
echo "  Frontend     → http://localhost:5173"
echo "  API          → http://127.0.0.1:8000/api/v1"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Press Ctrl+C to stop all servers"

wait $VITE_PID
