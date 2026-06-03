<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Budget extends Model {
    protected $fillable = ['year','month','revenue_target','expense_budget','purchase_budget','notes'];
    protected $casts    = ['revenue_target'=>'decimal:3','expense_budget'=>'decimal:3','purchase_budget'=>'decimal:3'];
    public function getMonthNameAttribute(): string { return date('F', mktime(0,0,0,$this->month,1)); }
}
