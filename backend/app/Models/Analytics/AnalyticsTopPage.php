<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsTopPage extends Model
{
    protected $table = 'analytics_top_pages';

    protected $fillable = [
        'date', 'page_path', 'views', 'users', 'bounce_rate',
        'avg_engagement_time', 'conversions', 'revenue_omr',
    ];

    protected $casts = [
        'date'        => 'date',
        'bounce_rate' => 'decimal:2',
        'revenue_omr' => 'decimal:3',
    ];
}
