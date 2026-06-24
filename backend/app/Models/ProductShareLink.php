<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductShareLink extends Model
{
    protected $fillable = ['name', 'token', 'product_ids', 'expires_at'];

    protected $casts = [
        'product_ids' => 'array',
        'expires_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductShareLink $link) {
            if (empty($link->token)) {
                do {
                    $token = Str::random(10);
                } while (static::where('token', $token)->exists());
                $link->token = $token;
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function products()
    {
        $ids = $this->product_ids ?? [];
        $products = Product::with('images')->whereIn('id', $ids)->where('is_active', true)->get();

        // Preserve the order the admin picked, not the DB's natural order
        return $products->sortBy(fn ($p) => array_search($p->id, $ids))->values();
    }
}
