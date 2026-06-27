<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDevice extends Model
{
    protected $table = 'analytics_device';

    protected $fillable = ['date', 'device_category', 'sessions', 'users'];

    protected $casts = ['date' => 'date'];
}
