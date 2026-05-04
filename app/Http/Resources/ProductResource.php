<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->slug,
            'model_number' => $this->model_number,
            'origin_country' => $this->getTranslations('origin_country'),
            'description' => $this->getTranslations('description'),
            'is_active' => (bool) $this->is_active,

            // جلب القسم والعلامة التجارية (إذا تم تحميلهما Eager Loading)
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'logo' => $this->brand->logo ? asset('storage/' . $this->brand->logo) : null,
                ];
            }),

            // جلب الصور (مع إضافة الرابط الكامل للسيرفر)
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->image_path),
                        'is_primary' => (bool) $img->is_primary
                    ];
                });
            }),

            // جلب المواصفات الفنية وتجميعها بناءً على (group_name)
            'specifications' => $this->whenLoaded('specifications', function () {
                return $this->specifications->groupBy('group_name')->map(function ($specs) {
                    return $specs->map(function ($spec) {
                        return [
                            'key' => $spec->spec_key,
                            'value' => $spec->spec_value,
                        ];
                    });
                });
            }),

            // جلب المميزات مرتبة
            'features' => $this->whenLoaded('features', function () {
                return $this->features->sortBy('sort_order')->values()->map(function ($feat) {
                    return $feat->feature_text;
                });
            }),

            // جلب خصائص الفلترة (مثل: اللون، السعة)
            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->map(function ($attrVal) {
                    return [
                        'type' => $attrVal->attribute->name,
                        'value' => $attrVal->value
                    ];
                });
            })
        ];
    }
}
