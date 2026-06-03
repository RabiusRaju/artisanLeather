<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CashFlowEntry extends Model {
    protected $fillable = [
        'type','category','description','amount_omr','entry_date',
        'payment_method','bank_reference','is_reconciled','notes',
    ];
    protected $casts = ['amount_omr'=>'decimal:3','entry_date'=>'date','is_reconciled'=>'boolean'];
}
