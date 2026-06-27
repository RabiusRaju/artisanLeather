<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsKeyword extends Model
{
    protected $table = 'analytics_keywords';

    protected $fillable = ['date', 'query', 'clicks', 'impressions', 'ctr', 'position'];

    protected $casts = [
        'date'     => 'date',
        'ctr'      => 'decimal:3',
        'position' => 'decimal:2',
    ];
}
