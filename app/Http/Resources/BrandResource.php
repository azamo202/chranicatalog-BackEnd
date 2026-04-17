<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // إرجاع الرابط الكامل للشعار إذا كان موجوداً
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
        ];
    }
}