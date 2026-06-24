<?php

use App\Http\Controllers\PrerenderController;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

// ── Social preview prerender (for Facebook/WhatsApp/Twitter/etc. bots) ─────
// The Caddy server in front of the static SPA at artisanleatherom.com should
// route bot requests for /blog/{slug}, /product/{slug} and /survey/{slug}
// here instead of serving the static index.html, so each page gets its own
// title/image instead of always the generic homepage info.
Route::get('/prerender/blog/{slug}',    [PrerenderController::class, 'blogPost']);
Route::get('/prerender/product/{slug}', [PrerenderController::class, 'product']);
Route::get('/prerender/survey/{slug}',  [PrerenderController::class, 'survey']);
Route::get('/prerender/share/{token}',  [PrerenderController::class, 'shareLink']);
Route::get('/prerender/home',           [PrerenderController::class, 'home']);
Route::get('/prerender/about',          [PrerenderController::class, 'about']);
Route::get('/prerender/contact',        [PrerenderController::class, 'contact']);
Route::get('/prerender/collections/{category?}', [PrerenderController::class, 'collections']);

// ── Dynamic XML Sitemap ────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $products = Product::where('is_active', true)
        ->select('slug', 'updated_at')
        ->orderBy('sort_order')
        ->get();

    $brands = Brand::where('is_active', true)
        ->select('slug', 'updated_at')
        ->get();

    $now = now()->toAtomString();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

    // ── Static pages ──────────────────────────────────────────────────────
    $static = [
        ['/', 1.0, 'weekly'],
        ['/collections', 0.9, 'weekly'],
        ['/collections/wallets', 0.8, 'weekly'],
        ['/collections/bags', 0.8, 'weekly'],
        ['/collections/belts', 0.8, 'weekly'],
        ['/collections/accessories', 0.8, 'weekly'],
        ['/about', 0.6, 'monthly'],
        ['/contact', 0.6, 'monthly'],
        ['/track', 0.4, 'monthly'],
    ];

    foreach ($static as [$path, $priority, $freq]) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://artisanleatherom.com{$path}</loc>\n";
        $xml .= "    <changefreq>{$freq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"https://artisanleatherom.com{$path}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"ar\" href=\"https://artisanleatherom.com{$path}?lang=ar\" />\n";
        $xml .= "  </url>\n";
    }

    // ── Product pages ─────────────────────────────────────────────────────
    foreach ($products as $product) {
        $lastmod = $product->updated_at->toAtomString();
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://artisanleatherom.com/product/{$product->slug}</loc>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.85</priority>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"https://artisanleatherom.com/product/{$product->slug}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"ar\" href=\"https://artisanleatherom.com/product/{$product->slug}?lang=ar\" />\n";
        $xml .= "  </url>\n";
    }

    // ── Collection brand pages ────────────────────────────────────────────
    foreach ($brands as $brand) {
        $lastmod = $brand->updated_at->toAtomString();
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://artisanleatherom.com/collections?brand={$brand->slug}</loc>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>0.7</priority>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "  </url>\n";
    }

    // ── Blog listing + individual posts ──────────────────────────────────
    $xml .= "  <url>\n";
    $xml .= "    <loc>https://artisanleatherom.com/blog</loc>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.75</priority>\n";
    $xml .= "    <lastmod>{$now}</lastmod>\n";
    $xml .= "  </url>\n";

    $posts = Post::published()->select('slug', 'updated_at')->latest('published_at')->get();
    foreach ($posts as $post) {
        $lastmod = $post->updated_at->toAtomString();
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://artisanleatherom.com/blog/{$post->slug}</loc>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>0.65</priority>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

// ── robots.txt served by Laravel ──────────────────────────────────────────
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\n\n";
    $content .= "# Private pages\n";
    $content .= "Disallow: /cart\nDisallow: /checkout\nDisallow: /account\n";
    $content .= "Disallow: /login\nDisallow: /register\nDisallow: /order-confirmation\n\n";
    $content .= "Sitemap: https://artisanleatherom.com/sitemap.xml\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

// ── Invoice print view — protected by admin auth ──────────────────────────
// C-4 FIX: Use Filament's panel middleware so auth redirect goes to admin login,
// not the non-existent web 'login' route. Also removed 'verified' — User model
// does not implement MustVerifyEmail so it was always a no-op.
Route::middleware(['panel:admin', Filament\Http\Middleware\Authenticate::class])
    ->group(function () {
        Route::get('/invoice/{order}', function (Order $order) {
            $order->loadMissing('items');
            return view('invoice.show', compact('order'));
        })->name('invoice.show');
    });
