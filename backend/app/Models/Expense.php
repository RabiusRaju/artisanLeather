<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model {
    protected $fillable = [
        'expense_category_id','supplier_id','title','description','amount_omr',
        'expense_date','payment_method','reference','receipt_image',
        'is_recurring','recurring_period','notes',
    ];
    protected $casts = ['amount_omr'=>'decimal:3','expense_date'=>'date','is_recurring'=>'boolean'];

    public function category(): BelongsTo  { return $this->belongsTo(ExpenseCategory::class,'expense_category_id'); }
    public function supplier(): BelongsTo  { return $this->belongsTo(Supplier::class); }
}
