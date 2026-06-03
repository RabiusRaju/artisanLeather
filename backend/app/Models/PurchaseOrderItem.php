<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model {
    protected $fillable = ['purchase_order_id','product_id','description','sku','quantity','unit','unit_cost_omr','total_cost_omr'];
    protected $casts    = ['quantity'=>'decimal:2','unit_cost_omr'=>'decimal:3','total_cost_omr'=>'decimal:3'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo       { return $this->belongsTo(Product::class); }
}
