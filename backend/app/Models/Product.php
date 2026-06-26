<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

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
    use LogsActivity;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'name_ar', 'slug', 'tagline', 'tagline_ar',
        'description', 'description_ar', 'material', 'material_ar',
        'origin', 'origin_ar', 'care', 'care_ar', 'shipping', 'shipping_ar',
        'price', 'badge', 'is_active', 'is_featured', 'sort_order',
        'meta_title', 'meta_description', 'shared_platforms',
        'sku', 'dimensions', 'dimensions_ar', 'bulk_pricing',
    ];

    protected $casts = [
        'price'            => 'decimal:3',
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'shared_platforms' => 'array',
        'bulk_pricing'     => 'array',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\ProductStock::class);
    }

    public function getInStockAttribute(): bool
    {
        return ($this->stock?->quantity ?? 0) > 0;
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->approvedReviews()->avg('rating'), 1);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Product \"{$this->name}\" was created",
                'updated' => "Product \"{$this->name}\" was updated",
                'deleted' => "Product \"{$this->name}\" was deleted",
                default => "Product \"{$this->name}\" {$eventName}",
            });
    }
}
