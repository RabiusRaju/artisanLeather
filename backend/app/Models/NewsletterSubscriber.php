<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'coupon_code',
        'source',
        'utm',
        'subscribed_at',
    ];

    protected $casts = [
        'utm' => 'array',
        'subscribed_at' => 'datetime',
    ];
}
