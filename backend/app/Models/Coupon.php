<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'is_active',
    ];

    protected $casts = [
        'value'     => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return round($subtotal * ((float) $this->value / 100), 3);
        }

        return min((float) $this->value, $subtotal);
    }
}
