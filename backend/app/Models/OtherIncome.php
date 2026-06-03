<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model {
    protected $table    = 'other_income';   // Laravel would guess 'other_incomes'
    protected $fillable = ['title','description','amount_omr','income_date','category','payment_method','reference','notes'];
    protected $casts    = ['amount_omr'=>'decimal:3','income_date'=>'date'];
}
