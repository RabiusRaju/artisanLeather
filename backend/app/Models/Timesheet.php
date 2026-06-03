<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    protected $fillable = [
        'craftsman_id','production_task_id','custom_order_id',
        'work_date','hours_worked','description','notes',
    ];
    protected $casts = [
        'work_date'    => 'date',
        'hours_worked' => 'decimal:2',
    ];

    public function craftsman(): BelongsTo { return $this->belongsTo(Craftsman::class); }
    public function productionTask(): BelongsTo { return $this->belongsTo(ProductionTask::class); }
    public function customOrder(): BelongsTo { return $this->belongsTo(CustomOrder::class); }

    public function getLaborCostAttribute(): float
    {
        return $this->hours_worked * ($this->craftsman?->hourly_rate_omr ?? 0);
    }
}
