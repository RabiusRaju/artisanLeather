<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Craftsman extends Model
{
    protected $fillable = [
        'name','name_ar','phone','speciality','hourly_rate_omr','is_active','notes',
    ];
    protected $casts = ['is_active' => 'boolean', 'hourly_rate_omr' => 'decimal:3'];

    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class); }
    public function productionTasks(): HasMany { return $this->hasMany(ProductionTask::class); }

    public function getHoursThisWeekAttribute(): float
    {
        return $this->timesheets()
            ->where('work_date', '>=', now()->startOfWeek())
            ->sum('hours_worked');
    }
}
