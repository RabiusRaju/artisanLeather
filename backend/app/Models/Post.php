<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Post extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title', 'title_ar', 'title_bn', 'slug', 'excerpt', 'excerpt_ar', 'excerpt_bn',
        'content', 'content_ar', 'content_bn', 'featured_image', 'featured_image_alt', 'category', 'tags',
        'author', 'meta_title', 'meta_description',
        'is_published', 'published_at', 'read_time', 'shared_platforms',
    ];

    protected $casts = [
        'tags'             => 'array',
        'is_published'     => 'boolean',
        'published_at'     => 'datetime',
        'shared_platforms' => 'array',
    ];

    // Scope: only published posts for the public API
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    // Auto-calculate read time when content changes
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if ($post->isDirty('content')) {
                $words = str_word_count(strip_tags($post->content));
                $post->read_time = max(1, (int) ceil($words / 200));
            }
            if (empty($post->slug) && $post->title) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => match (true) {
                $eventName === 'created' => "Blog post \"{$this->title}\" was created",
                $eventName === 'updated' && $this->is_published => "Blog post \"{$this->title}\" was published",
                $eventName === 'updated' => "Blog post \"{$this->title}\" was updated",
                $eventName === 'deleted' => "Blog post \"{$this->title}\" was deleted",
                default => "Blog post \"{$this->title}\" {$eventName}",
            });
    }
}
