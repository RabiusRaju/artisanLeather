<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'name','name_ar','slug','tagline','tagline_ar',
        'description','description_ar','logo','banner','website',
        'is_active','is_featured','sort_order',
    ];
    protected $casts = ['is_active' => 'boolean', 'is_featured' => 'boolean'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
