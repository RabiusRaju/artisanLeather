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
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';

        $query = Post::published()->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $posts = $query->get()->map(fn($p) => $this->format($p, $locale));

        return response()->json(['data' => $posts]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';

        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->format($post, $locale, full: true)]);
    }

    private function format(Post $post, string $locale, bool $full = false): array
    {
        $isAr = $locale === 'ar';
        $base  = [
            'id'              => $post->id,
            'title'           => $isAr && $post->title_ar ? $post->title_ar : $post->title,
            'title_en'        => $post->title,
            'title_ar'        => $post->title_ar,
            'slug'            => $post->slug,
            'excerpt'         => $isAr && $post->excerpt_ar ? $post->excerpt_ar : $post->excerpt,
            'excerpt_en'      => $post->excerpt,
            'excerpt_ar'      => $post->excerpt_ar,
            'featured_image'  => $post->featured_image
                ? (str_starts_with($post->featured_image, 'http')
                    ? $post->featured_image
                    : asset('storage/' . $post->featured_image))
                : null,
            'featured_image_alt' => $post->featured_image_alt ?: (($isAr && $post->title_ar ? $post->title_ar : $post->title) . ' | Artisan Leather Journal'),
            'category'        => $post->category,
            'tags'            => $post->tags ?? [],
            'author'          => $post->author,
            'read_time'       => $post->read_time,
            'published_at'    => $post->published_at?->toISOString(),
            'meta_title'      => $post->meta_title,
            'meta_description'=> $post->meta_description,
        ];

        if ($full) {
            $base['content']    = VideoEmbedder::embed($isAr && $post->content_ar ? $post->content_ar : $post->content);
            $base['content_en'] = VideoEmbedder::embed($post->content);
            $base['content_ar'] = VideoEmbedder::embed($post->content_ar);
        }

        return $base;
    }
}
