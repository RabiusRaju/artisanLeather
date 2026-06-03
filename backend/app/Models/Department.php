<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model {
    protected $fillable = ['name','name_ar','description','is_active','sort_order'];
    protected $casts    = ['is_active' => 'boolean'];
    public function employees(): HasMany { return $this->hasMany(Employee::class); }
}
