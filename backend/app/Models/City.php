<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = ['governorate_id','name','name_ar','is_active','sort_order'];
    protected $casts    = ['is_active' => 'boolean'];

    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
}
