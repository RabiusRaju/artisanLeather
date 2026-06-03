<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $category_id
 * @property string      $name
 * @property string|null $name_ar
 * @property string      $slug
 * @property string|null $tagline
 * @property string|null $tagline_ar
 * @property string|null $description
 * @property string|null $description_ar
 * @property string|null $material
 * @property string|null $material_ar
 * @property string|null $origin
 * @property string|null $origin_ar
 * @property string|null $care
 * @property string|null $care_ar
 * @property string|null $shipping
 * @property string|null $shipping_ar
 * @property float        $price
 * @property string|null $badge
 * @property bool         $is_active
 * @property bool         $is_featured
 * @property int          $sort_order
 */
class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'name_ar', 'slug', 'tagline', 'tagline_ar',
        'description', 'description_ar', 'material', 'material_ar',
        'origin', 'origin_ar', 'care', 'care_ar', 'shipping', 'shipping_ar',
        'price', 'badge', 'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'price'       => 'decimal:3',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProductDetail::class)->orderBy('sort_order');
    }
}
