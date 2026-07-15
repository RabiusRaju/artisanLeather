<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\VideoEmbedder;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->locale($request);

        $query = Post::published()->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $posts = $query->get()->map(fn($p) => $this->format($p, $locale));

        return response()->json(['data' => $posts]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = $this->locale($request);

        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->format($post, $locale, full: true)]);
    }

    private function format(Post $post, string $locale, bool $full = false): array
    {
        $isAr = $locale === 'ar';
        $isBn = $locale === 'bn';
        $title = match (true) {
            $isAr && filled($post->title_ar) => $post->title_ar,
            $isBn && filled($post->title_bn) => $post->title_bn,
            default => $post->title,
        };
        $excerpt = match (true) {
            $isAr && filled($post->excerpt_ar) => $post->excerpt_ar,
            $isBn && filled($post->excerpt_bn) => $post->excerpt_bn,
            default => $post->excerpt,
        };
        $metaTitle = match (true) {
            $isAr && filled($post->meta_title_ar) => $post->meta_title_ar,
            $isBn && filled($post->meta_title_bn) => $post->meta_title_bn,
            default => $post->meta_title,
        };
        $metaDescription = match (true) {
            $isAr && filled($post->meta_description_ar) => $post->meta_description_ar,
            $isBn && filled($post->meta_description_bn) => $post->meta_description_bn,
            default => $post->meta_description,
        };

        $base  = [
            'id'              => $post->id,
            'title'           => $title,
            'title_en'        => $post->title,
            'title_ar'        => $post->title_ar,
            'title_bn'        => $post->title_bn,
            'slug'            => $post->slug,
            'excerpt'         => $excerpt,
            'excerpt_en'      => $post->excerpt,
            'excerpt_ar'      => $post->excerpt_ar,
            'excerpt_bn'      => $post->excerpt_bn,
            'featured_image'  => $post->featured_image
                ? (str_starts_with($post->featured_image, 'http')
                    ? $post->featured_image
                    : asset('storage/' . $post->featured_image))
                : null,
            'featured_image_alt' => $post->featured_image_alt ?: ($title . ' | Artisan Leather Journal'),
            'category'        => $post->category,
            'tags'            => $post->tags ?? [],
            'author'          => $post->author,
            'read_time'       => $post->read_time,
            'published_at'    => $post->published_at?->toISOString(),
            'meta_title'      => $metaTitle,
            'meta_description'=> $metaDescription,
            'meta_title_en'   => $post->meta_title,
            'meta_description_en' => $post->meta_description,
            'meta_title_ar'   => $post->meta_title_ar,
            'meta_description_ar' => $post->meta_description_ar,
            'meta_title_bn'   => $post->meta_title_bn,
            'meta_description_bn' => $post->meta_description_bn,
        ];

        if ($full) {
            $content = match (true) {
                $isAr && filled($post->content_ar) => $post->content_ar,
                $isBn && filled($post->content_bn) => $post->content_bn,
                default => $post->content,
            };
            $base['content']    = VideoEmbedder::embed($content);
            $base['content_en'] = VideoEmbedder::embed($post->content);
            $base['content_ar'] = VideoEmbedder::embed($post->content_ar);
            $base['content_bn'] = VideoEmbedder::embed($post->content_bn);
        }

        return $base;
    }

    private function locale(Request $request): string
    {
        $language = strtolower($request->header('Accept-Language', 'en'));

        return match (true) {
            str_starts_with($language, 'ar') => 'ar',
            str_starts_with($language, 'bn') => 'bn',
            default => 'en',
        };
    }
}
