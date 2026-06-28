<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsStagingItem extends Model
{
    protected $fillable = [
        'source_name', 'source_url', 'article_url', 'title', 'excerpt',
        'published_at', 'status', 'generated_post_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function generatedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'generated_post_id');
    }
}
