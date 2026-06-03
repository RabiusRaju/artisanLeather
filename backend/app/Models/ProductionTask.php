<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionTask extends Model
{
    protected $fillable = [
        'title','description','task_type',
        'order_id','custom_order_id','craftsman_id',
        'priority','status',
        'estimated_hours','actual_hours',
        'due_date','completed_at','notes',
    ];
    protected $casts = [
        'due_date'      => 'date',
        'completed_at'  => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours'    => 'decimal:2',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function customOrder(): BelongsTo { return $this->belongsTo(CustomOrder::class); }
    public function craftsman(): BelongsTo { return $this->belongsTo(Craftsman::class); }
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class); }

    public function getLinkedReferenceAttribute(): string
    {
        return $this->customOrder?->reference_number
            ?? $this->order?->order_number
            ?? '—';
    }
}
