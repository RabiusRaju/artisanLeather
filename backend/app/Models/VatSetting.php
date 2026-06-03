<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VatSetting extends Model {
    protected $fillable = ['rate','registration_number','effective_from','is_registered','notes'];
    protected $casts    = ['is_registered'=>'boolean','effective_from'=>'date','rate'=>'decimal:2'];
    public static function currentRate(): float { return (float) (static::latest()->first()?->rate ?? 5.00); }
}
