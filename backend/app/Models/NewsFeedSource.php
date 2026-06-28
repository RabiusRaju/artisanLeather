<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsFeedSource extends Model
{
    protected $fillable = [
        'name',
        'feed_url',
        'is_active',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
