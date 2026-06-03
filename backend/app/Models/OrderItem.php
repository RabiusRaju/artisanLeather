<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_name_ar',
        'color_name', 'color_hex', 'quantity',
        'unit_price_omr', 'total_price_omr',
    ];

    protected $casts = [
        'unit_price_omr'  => 'decimal:3',
        'total_price_omr' => 'decimal:3',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
