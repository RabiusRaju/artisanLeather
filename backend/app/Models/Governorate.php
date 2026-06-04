<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    protected $fillable = ['name','name_ar','code','country_code','is_active','sort_order'];
    protected $casts    = ['is_active' => 'boolean'];

    public function cities(): HasMany { return $this->hasMany(City::class)->orderBy('sort_order'); }

    public static function activeOptions(string $locale = 'en'): array
    {
        return static::where('is_active', true)->orderBy('sort_order')
            ->get()->mapWithKeys(fn($g) => [
                $g->name => $locale === 'ar' && $g->name_ar ? $g->name_ar : $g->name
            ])->toArray();
    }
}
