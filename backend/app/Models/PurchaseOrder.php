<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model {
    protected $fillable = [
        'reference_number','supplier_id','order_date','expected_delivery','actual_delivery',
        'status','currency','exchange_rate','subtotal_omr','shipping_cost_omr',
        'customs_duty_omr','other_costs_omr','total_omr',
        'payment_status','paid_amount_omr','payment_date','notes',
    ];
    protected $casts = [
        'order_date'=>'date','expected_delivery'=>'date','actual_delivery'=>'date','payment_date'=>'date',
        'subtotal_omr'=>'decimal:3','shipping_cost_omr'=>'decimal:3','customs_duty_omr'=>'decimal:3',
        'other_costs_omr'=>'decimal:3','total_omr'=>'decimal:3','paid_amount_omr'=>'decimal:3',
    ];

    protected static function booted(): void {
        static::creating(function (PurchaseOrder $po) {
            if (!$po->reference_number) {
                $po->reference_number = 'PO-'.date('Y').'-'.str_pad(random_int(10000,99999),5,'0',STR_PAD_LEFT);
            }
        });
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany      { return $this->hasMany(PurchaseOrderItem::class); }

    public function getBalanceDueAttribute(): float { return $this->total_omr - $this->paid_amount_omr; }
}
