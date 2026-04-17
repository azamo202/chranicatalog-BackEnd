<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            // التأكد من إرجاع الرابط الكامل للصورة إذا كانت موجودة
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'is_active' => (bool) $this->is_active,
            'parent_id' => $this->parent_id,
            // استدعاء الأقسام الفرعية إذا تم طلبها (Eager Loading)
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}