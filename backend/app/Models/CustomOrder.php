<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomOrder extends Model
{
    protected $fillable = [
        'reference_number','customer_id','customer_name','customer_phone',
        'product_type','product_name','description',
        'leather_color','leather_type','stitching_color','hardware_color','size',
        'monogram','personalisation_notes','reference_images',
        'agreed_price_omr','deposit_amount_omr','deposit_paid','deposit_paid_at',
        'status','promised_date','delivered_at','whatsapp_thread','admin_notes',
    ];
    protected $casts = [
        'reference_images' => 'array',
        'deposit_paid'     => 'boolean',
        'deposit_paid_at'  => 'datetime',
        'promised_date'    => 'date',
        'delivered_at'     => 'date',
        'agreed_price_omr' => 'decimal:3',
        'deposit_amount_omr' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomOrder $order) {
            if (!$order->reference_number) {
                $order->reference_number = 'CUS-' . date('Y') . '-' . str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function productionTasks(): HasMany { return $this->hasMany(ProductionTask::class); }
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class); }

    public function getBalanceDueAttribute(): float
    {
        return $this->agreed_price_omr - $this->deposit_amount_omr;
    }
}
