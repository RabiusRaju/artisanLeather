<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'is_active',
        'show_as_popup', 'expires_at', 'popup_title', 'popup_image',
    ];

    protected $casts = [
        'value'         => 'decimal:3',
        'is_active'     => 'boolean',
        'show_as_popup' => 'boolean',
        'expires_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        // Only one coupon can be the featured popup at a time.
        static::saving(function (Coupon $coupon) {
            if ($coupon->show_as_popup) {
                static::where('id', '!=', $coupon->id ?? 0)->update(['show_as_popup' => false]);
            }
        });
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return round($subtotal * ((float) $this->value / 100), 3);
        }

        return min((float) $this->value, $subtotal);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
