<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductShareLink;
use App\Models\Setting;
use App\Models\Survey;
use Illuminate\Http\Request;

class PrerenderController extends Controller
{
    // Social/search crawlers that don't execute JavaScript — they only ever see
    // raw server HTML. The Caddy front server should route requests with these
    // user agents to these endpoints instead of the static SPA, so each page
    // gets its own correct title/description/image instead of the homepage's.
    public static function isBot(Request $request): bool
    {
        $ua = (string) $request->userAgent();
        if ($ua === '') {
            return false;
        }

        $bots = [
            'facebookexternalhit', 'Facebot', 'Twitterbot', 'LinkedInBot',
            'WhatsApp', 'TelegramBot', 'Slackbot', 'Discordbot', 'Pinterest',
            'vkShare', 'SkypeUriPreview', 'Googlebot', 'bingbot', 'Applebot',
        ];

        foreach ($bots as $bot) {
            if (stripos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    // GET /prerender/blog  (listing page)
    public function blogIndex(Request $request)
    {
        $url = 'https://artisanleatherom.com/blog';

        if (!self::isBot($request)) {
            return redirect($url);
        }

        return view('prerender.meta', [
            'title'       => 'The Leather Journal — Care Guides & Style Tips',
            'description' => 'Expert leather care guides, style tips, and stories from the artisans at Artisan Leather, Muscat Oman.',
            'image'       => 'https://artisanleatherom.com/og-image.jpg',
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/track
    public function track(Request $request)
    {
        $url = 'https://artisanleatherom.com/track';

        if (!self::isBot($request)) {
            return redirect($url);
        }

        return view('prerender.meta', [
            'title'       => 'Track Your Order',
            'description' => 'Track your Artisan Leather order status in real-time.',
            'image'       => 'https://artisanleatherom.com/og-image.jpg',
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/blog/{slug}
    public function blogPost(Request $request, string $slug)
    {
        $url = "https://artisanleatherom.com/blog/{$slug}";

        // Defence in depth: if a human reaches this route directly (Caddy
        // misconfigured, or someone shared this URL), send them straight to
        // the real SPA page instead of showing the bare meta-only HTML.
        if (!self::isBot($request)) {
            return redirect($url);
        }

        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $image = $post->featured_image
            ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image))
            : null;

        return view('prerender.meta', [
            'title'       => $post->meta_title ?: $post->title,
            'description' => $post->meta_description ?: $post->excerpt,
            'image'       => $image,
            'url'         => $url,
            'type'        => 'article',
        ]);
    }

    // GET /prerender/product/{slug}
    public function product(Request $request, string $slug)
    {
        $url = "https://artisanleatherom.com/product/{$slug}";

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $product   = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $imagePath = $product->images->first()?->url;
        $image     = $imagePath
            ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath))
            : null;

        return view('prerender.meta', [
            'title'       => $product->meta_title ?: $product->name,
            'description' => $product->meta_description ?: $product->tagline,
            'image'       => $image,
            'url'         => $url,
            'type'        => 'product',
        ]);
    }

    // GET /prerender/survey/{slug}
    public function survey(Request $request, string $slug)
    {
        $url = "https://artisanleatherom.com/survey/{$slug}";

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $survey = Survey::where('slug', $slug)->firstOrFail();

        // Surveys have no dedicated image field — fall back to the first
        // question's image (if any), otherwise no og:image at all.
        $firstQuestionImage = $survey->questions()->whereNotNull('image_path')->value('image_path');
        $image = $firstQuestionImage ? asset('storage/' . $firstQuestionImage) : null;

        return view('prerender.meta', [
            'title'       => $survey->title,
            'description' => $survey->description ?: 'Share your feedback with Artisan Leather — it only takes a minute.',
            'image'       => $image,
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/share/{token}
    public function shareLink(Request $request, string $token)
    {
        $url = "https://artisanleatherom.com/share/{$token}";

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $link = ProductShareLink::where('token', $token)->firstOrFail();
        if ($link->isExpired()) {
            abort(404);
        }

        $products = $link->products();
        $firstProduct = $products->first();
        $imagePath = $firstProduct?->images->first()?->url;
        $image = $imagePath
            ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath))
            : null;

        $count = $products->count();
        $description = $count > 0
            ? "A curated selection of {$count} product" . ($count === 1 ? '' : 's') . " from Artisan Leather."
            : 'A curated selection of products from Artisan Leather.';

        return view('prerender.meta', [
            'title'       => $link->name ?: 'A Curated Selection — Artisan Leather',
            'description' => $description,
            'image'       => $image,
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/  (homepage)
    public function home(Request $request)
    {
        $url = 'https://artisanleatherom.com/';

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $settings = Setting::pluck('value', 'key');

        return view('prerender.meta', [
            'title'       => $settings['homepage.seo.meta_title'] ?: 'Luxury Leather Wallets, Bags & Accessories',
            'description' => $settings['homepage.seo.meta_description'] ?: 'Discover premium handcrafted leather wallets, bags, belts and accessories from Artisan Leather, Muscat Oman. Free delivery across Oman and GCC. Shop now.',
            'image'       => 'https://artisanleatherom.com/og-image.jpg',
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/about
    public function about(Request $request)
    {
        $url = 'https://artisanleatherom.com/about';

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $settings = Setting::pluck('value', 'key');

        return view('prerender.meta', [
            'title'       => $settings['about.seo.meta_title'] ?: 'Our Story — Leather Artisans, Muscat',
            'description' => $settings['about.seo.meta_description'] ?: "Learn about Artisan Leather's heritage, craftsmanship philosophy, and the skilled artisans behind every handcrafted leather piece made in Muscat, Oman.",
            'image'       => 'https://artisanleatherom.com/og-image.jpg',
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/contact
    public function contact(Request $request)
    {
        $url = 'https://artisanleatherom.com/contact';

        if (!self::isBot($request)) {
            return redirect($url);
        }

        return view('prerender.meta', [
            'title'       => 'Contact Us — Muscat, Oman',
            'description' => "Contact Artisan Leather via WhatsApp, email or our online form. We're based in Muscat, Oman and deliver across the GCC. Custom orders and enquiries welcome.",
            'image'       => 'https://artisanleatherom.com/og-image.jpg',
            'url'         => $url,
            'type'        => 'website',
        ]);
    }

    // GET /prerender/collections/{category?}
    public function collections(Request $request, ?string $category = null)
    {
        $url = 'https://artisanleatherom.com/collections' . ($category ? "/{$category}" : '');
        $brandSlug = $request->query('brand');
        if ($brandSlug) {
            $url .= '?brand=' . $brandSlug;
        }

        if (!self::isBot($request)) {
            return redirect($url);
        }

        $activeBrand    = $brandSlug ? Brand::where('slug', $brandSlug)->where('is_active', true)->first() : null;
        $activeCategory = $category ? Category::where('slug', $category)->first() : null;

        $productQuery = Product::with('images')->where('is_active', true);
        if ($activeBrand) {
            $productQuery->where('brand_id', $activeBrand->id);
        } elseif ($activeCategory) {
            $productQuery->where('category_id', $activeCategory->id);
        }
        $imagePath = $productQuery->first()?->images->first()?->url;
        $image = $imagePath
            ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath))
            : 'https://artisanleatherom.com/og-image.jpg';

        if ($activeBrand) {
            $title       = "{$activeBrand->name} — Handcrafted Leather";
            $description = "Shop {$activeBrand->name} — handcrafted leather goods made by artisans in Muscat, Oman. Free delivery across Oman and GCC.";
        } elseif ($category) {
            $categoryLabel = $activeCategory?->name ?: ucfirst($category);
            $title       = "{$categoryLabel} — Handcrafted Leather Goods";
            $description = "Browse our handcrafted leather {$category} collection. Premium quality, made by artisans in Muscat, Oman. Free delivery across Oman and GCC.";
        } else {
            $title       = 'All Collections — Handcrafted Leather';
            $description = 'Explore the full Artisan Leather collection — wallets, bags, belts and accessories. All handcrafted in Muscat, Oman. Free delivery across Oman and GCC.';
        }

        return view('prerender.meta', [
            'title'       => $title,
            'description' => $description,
            'image'       => $image,
            'url'         => $url,
            'type'        => 'website',
        ]);
    }
}
