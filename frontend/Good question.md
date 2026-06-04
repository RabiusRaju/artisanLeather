Date: 4 June 2026 | Tester: Senior QA Review | System: Laravel 13 + Filament 4

🔴 CRITICAL — Must Fix Before Go-Live (6 issues)
#	Issue	File	Impact
C-1	Price Manipulation Attack — API accepts unit_price from client, not from DB. Verified: ordered OMR 45 product for OMR 0.001	OrderController.php:37	Revenue loss
C-2	APP_DEBUG=true exposes full stack traces — API exceptions return server paths, versions, full trace	.env:5	Security breach
C-3	robots.txt is dead — Caddy's file_server serves a blank static file before Laravel's route runs. Correct disallows never reach Google	public/robots.txt + Caddyfile	SEO harm
C-4	Invoice route crashes with 500 — middleware('auth') redirects to named route login which doesn't exist; only filament.admin.auth.login exists	routes/web.php:111	500 error for all invoice links
C-5	Survey deduplication bypassed — Omitting X-Survey-Token header generates a fresh token each time. Verified: submitted 17 responses with no block	SurveyController.php:69	Polluted survey data
C-6	No stock decrement on order — When a customer places an order, ProductStock.quantity is never updated. Inventory is always wrong	OrderController.php	Overselling
🟠 HIGH — Fix Soon (6 issues)
#	Issue	File
H-1	Brand banner/logo URLs double-prefixed — Seeded external URLs get wrapped in asset('storage/...') → broken http://127.0.0.1:8000/storage/https://...	BrandController.php:25
H-2	Zero API rate limiting — No throttle on orders, login, or order tracking. Login is brute-forceable	routes/api.php
H-3	Order numbers enumerable — Only 90,000 combinations per year. /api/v1/track/{number} returns customer name, address, items with no auth	Order.php:35
H-4	Dashboard runs 50+ uncached queries — StatsOverview: 22+ queries. RevenueTrendWidget: 30 separate whereDate()->sum() queries. No caching anywhere	All dashboard widgets
H-5	Test data publicly visible — Categories named "Wallets 1", brands named "Executive Line Raju 1" returned via public API and indexed by Google	Database
H-6	No Role-Based Access Control — Any registered customer account can attempt admin login. No is_admin check exists. Filament User and Sanctum API tokens share the same table	AdminPanelProvider.php
🟡 MEDIUM — Next Sprint (7 issues)
#	Issue
M-1	Missing DB indexes on orders.status, orders.created_at, orders.email, posts.is_published — every dashboard query does a full table scan
M-2	No pagination on Product/Post/Brand APIs — ->get() with no limit. Will time out as data grows
M-3	Category images never served — Controller returns raw path without asset('storage/...') wrapper
M-4	Survey answers not validated against survey — Submitted question IDs not checked to belong to the current survey
M-5	Order number race condition — No retry loop on collision → 500 error on concurrent orders
M-6	Password reset not configured — Admin locked out = manual DB intervention required
M-7	MAIL_MAILER=log — No customer will receive any email after go-live