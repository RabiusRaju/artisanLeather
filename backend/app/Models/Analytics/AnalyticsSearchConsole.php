<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSearchConsole extends Model
{
    protected $table = 'analytics_search_console';

    protected $fillable = ['date', 'clicks', 'impressions', 'ctr', 'position'];

    protected $casts = [
        'date'     => 'date',
        'ctr'      => 'decimal:3',
        'position' => 'decimal:2',
    ];
}
