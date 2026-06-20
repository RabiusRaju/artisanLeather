<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'slug', 'title', 'title_ar', 'last_updated', 'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];
}
