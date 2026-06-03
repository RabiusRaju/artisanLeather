<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStock extends Model {
    protected $table    = 'product_stock';
    protected $fillable = ['product_id','quantity','minimum_alert','reorder_qty','location','notes'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function movements(): HasMany  { return $this->hasMany(StockMovement::class, 'product_id', 'product_id'); }

    public function isLow(): bool      { return $this->quantity <= $this->minimum_alert; }
    public function isOutOfStock(): bool { return $this->quantity <= 0; }
    public function getStatusAttribute(): string {
        if ($this->quantity <= 0)               return 'out_of_stock';
        if ($this->quantity <= $this->minimum_alert) return 'low';
        return 'in_stock';
    }
}
