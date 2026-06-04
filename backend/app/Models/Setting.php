<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'group', 'value', 'type', 'label', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, fn() =>
            static::where('key', $key)->value('value') ?? $default
        );
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    public static function group(string $group): array
    {
        return Cache::remember("settings:group:{$group}", 3600, fn() =>
            static::where('group', $group)->pluck('value', 'key')->toArray()
        );
    }

    protected static function booted(): void
    {
        static::saved(fn($s) => Cache::forget("setting:{$s->key}"));
        static::deleted(fn($s) => Cache::forget("setting:{$s->key}"));
    }
}
