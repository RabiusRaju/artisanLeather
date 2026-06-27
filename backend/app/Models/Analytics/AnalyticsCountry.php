<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsCountry extends Model
{
    protected $table = 'analytics_country';

    protected $fillable = ['date', 'country', 'sessions', 'users'];

    protected $casts = ['date' => 'date'];
}
