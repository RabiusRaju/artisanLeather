<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
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

        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $image   = $product->images->first()?->url;

        return view('prerender.meta', [
            'title'       => $product->meta_title ?: $product->name,
            'description' => $product->meta_description ?: $product->tagline,
            'image'       => $image,
            'url'         => $url,
            'type'        => 'product',
        ]);
    }
}
