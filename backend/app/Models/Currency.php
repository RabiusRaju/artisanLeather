<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'symbol', 'name', 'name_ar', 'rate', 'decimals', 'is_active', 'sort_order'];

    protected $casts = [
        'rate'      => 'decimal:6',
        'is_active' => 'boolean',
    ];
}
