<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'          => $this->id,
            'name'        => $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name,
            'name_en'     => $this->name,
            'name_ar'     => $this->name_ar,
            'slug'        => $this->slug,
            'tagline'     => $locale === 'ar' && $this->tagline_ar ? $this->tagline_ar : $this->tagline,
            'tagline_ar'  => $this->tagline_ar,
            'description'    => $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description,
            'description_ar' => $this->description_ar,
            'material'       => $locale === 'ar' && $this->material_ar ? $this->material_ar : $this->material,
            'material_ar'    => $this->material_ar,
            'origin'         => $locale === 'ar' && $this->origin_ar ? $this->origin_ar : $this->origin,
            'origin_ar'      => $this->origin_ar,
            'care'           => $locale === 'ar' && $this->care_ar ? $this->care_ar : $this->care,
            'care_ar'        => $this->care_ar,
            'shipping'       => $locale === 'ar' && $this->shipping_ar ? $this->shipping_ar : $this->shipping,
            'shipping_ar'    => $this->shipping_ar,
            'price'       => (float) $this->price,
            'badge'       => $this->badge,
            'is_featured' => $this->is_featured,
            'in_stock'    => $this->in_stock,

            // SEO fields — custom if set, otherwise null (frontend falls back gracefully)
            'meta_title'       => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'category'    => [
                'id'   => $this->category->id,
                'name' => $locale === 'ar' && $this->category->name_ar ? $this->category->name_ar : $this->category->name,
                'slug' => $this->category->slug,
            ],
            'brand' => $this->brand ? [
                'id'          => $this->brand->id,
                'name'        => $locale === 'ar' && $this->brand->name_ar ? $this->brand->name_ar : $this->brand->name,
                'name_en'     => $this->brand->name,
                'name_ar'     => $this->brand->name_ar,
                'slug'        => $this->brand->slug,
                'tagline'     => $locale === 'ar' && $this->brand->tagline_ar ? $this->brand->tagline_ar : $this->brand->tagline,
                'logo'        => $this->brand->logo ? asset('storage/' . $this->brand->logo) : null,
                'is_featured' => $this->brand->is_featured,
            ] : null,
            'images' => $this->images->map(fn($img) => [
                'id'         => $img->id,
                'url'        => $img->url
                    ? (Str::startsWith($img->url, ['http://', 'https://']) ? $img->url : asset('storage/' . $img->url))
                    : null,
                'label'      => $img->label,
                'alt_text'   => $img->alt_text
                    ?: ($this->name . ($img->label ? ' — ' . $img->label : '') . ' | Artisan Leather Oman'),
                'sort_order' => $img->sort_order,
            ]),
            'colors' => $this->colors->map(fn($c) => [
                'name'    => $locale === 'ar' && $c->name_ar ? $c->name_ar : $c->name,
                'name_en' => $c->name,
                'name_ar' => $c->name_ar,
                'hex'     => $c->hex,
            ]),
            'details' => $this->details->map(fn($d) => [
                'detail'    => $locale === 'ar' && $d->detail_ar ? $d->detail_ar : $d->detail,
                'detail_ar' => $d->detail_ar,
            ]),
        ];
    }
}
