<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model {
    protected $fillable = ['name','name_ar','icon','color','type','description','is_active','sort_order'];
    protected $casts    = ['is_active'=>'boolean'];
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}
