<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model {
    protected $fillable = [
        'name','name_ar','country','contact_person','phone','whatsapp','email','website',
        'address','category','payment_terms','currency','credit_limit_omr',
        'lead_time_days','rating','notes','is_active',
    ];
    protected $casts = ['is_active'=>'boolean','credit_limit_omr'=>'decimal:3'];

    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }
    public function expenses(): HasMany       { return $this->hasMany(Expense::class); }

    public function getTotalPurchasesAttribute(): float
    {
        return $this->purchaseOrders()->whereIn('status',['received','partial'])->sum('total_omr');
    }
}
