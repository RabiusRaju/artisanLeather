<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'first_name', 'last_name', 'email', 'phone',
        'governorate', 'city', 'address', 'notes',
        'payment_method', 'currency_code', 'currency_rate',
        'subtotal_omr', 'total_omr', 'status', 'admin_notes',
        'coupon_code', 'discount_amount',
    ];

    protected $casts = [
        'currency_rate'    => 'decimal:6',
        'subtotal_omr'     => 'decimal:3',
        'total_omr'        => 'decimal:3',
        'discount_amount'  => 'decimal:3',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public static function generateOrderNumber(): string
    {
        return 'AL-' . date('Y') . '-' . str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
    }
}
