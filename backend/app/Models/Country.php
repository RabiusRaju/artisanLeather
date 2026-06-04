<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name','name_ar','code','dial_code','flag_emoji','is_active','is_gcc','sort_order'];
    protected $casts    = ['is_active' => 'boolean', 'is_gcc' => 'boolean'];

    public static function activeOptions(string $locale = 'en'): array
    {
        return static::where('is_active', true)->orderBy('sort_order')
            ->get()->mapWithKeys(fn($c) => [
                $c->name => ($c->flag_emoji ? $c->flag_emoji . ' ' : '') .
                            ($locale === 'ar' && $c->name_ar ? $c->name_ar : $c->name)
            ])->toArray();
    }
}
