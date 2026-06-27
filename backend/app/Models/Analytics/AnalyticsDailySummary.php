<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailySummary extends Model
{
    protected $table = 'analytics_daily_summary';

    protected $fillable = [
        'date', 'sessions', 'users', 'new_users', 'page_views',
        'bounce_rate', 'avg_engagement_time', 'conversions',
    ];

    protected $casts = [
        'date'       => 'date',
        'bounce_rate' => 'decimal:2',
    ];
}
