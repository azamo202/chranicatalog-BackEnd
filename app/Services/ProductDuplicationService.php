<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductDuplicationService
{
    /**
     * تكرار منتج بالكامل مع جميع علاقاته داخل Transaction واحدة.
     *
     * ما يتم نسخه:
     *  - جميع بيانات المنتج الأساسية (الاسم، الوصف، القسم، الماركة، إلخ)
     *  - الصور (روابط قاعدة البيانات — دون نسخ الملفات الفيزيائية)
     *  - المواصفات الفنية (Specifications)
     *  - المميزات (Features)
     *  - قيم الخصائص (Attribute Values — M2M)
     *
     * ما لا يتم نسخه:
     *  - المعرّف (ID)
     *  - الـ Slug (يُولَّد جديد)
     *  - created_at / updated_at
     *  - أقسام الصفحة الرئيسية (خيارات تحريرية يختارها المستخدم)
     *
     * @param Product $original المنتج الأصلي المراد تكراره
     * @return Product المنتج الجديد المُنشأ
     *
     * @throws \Throwable عند فشل أي خطوة (يتم Rollback تلقائي)
     */
    public function duplicate(Product $original): Product
    {
        return DB::transaction(function () use ($original) {

            // 1. تحميل المنتج الأصلي مع جميع علاقاته المطلوبة
            $original->load(['images', 'specifications', 'features', 'attributeValues']);

            // 2. بناء اسم المنتج الجديد — نضيف (نسخة) للاسم العربي
            $originalNames = $original->getTranslations('name');
            $newNames       = $originalNames;

            if (!empty($newNames['ar'])) {
                $newNames['ar'] = $newNames['ar'] . ' (نسخة)';
            }

            // 3. توليد Slug فريد بناءً على الاسم الإنجليزي أو العربي
            $slugBase = $newNames['en'] ?? $newNames['ar'] ?? 'product';
            $newSlug  = Str::slug($slugBase) . '-' . uniqid();

            // 4. تحديد sort_order في نهاية القسم الحالي
            $maxSortOrder = Product::where('category_id', $original->category_id)
                ->lockForUpdate()
                ->max('sort_order') ?? 0;

            // 5. إنشاء سجل المنتج الجديد
            $newProduct = Product::create([
                'name'           => $newNames,
                'slug'           => $newSlug,
                'category_id'    => $original->category_id,
                'brand_id'       => $original->brand_id,
                'model_number'   => $original->model_number,
                'origin_country' => $original->getTranslations('origin_country'),
                'description'    => $original->getTranslations('description'),
                'is_active'      => $original->is_active,
                'sort_order'     => $maxSortOrder + 1,
            ]);

            // 6. تكرار الصور — نسخ فيزيائي لكل ملف لضمان الاستقلالية
            //
            // السبب: deleteImage() يحذف الملف من Storage فيزيائياً.
            // لو شاركنا نفس الـ image_path بين المنتجين، وحذف المستخدم صورة من
            // المنتج الأصلي، ستُحذف الملف وتنكسر صور المنتج المكرّر.
            // الحل: نسخ فيزيائي للملف مع مسار جديد، فكل منتج يملك نسخته الخاصة.
            foreach ($original->images as $image) {
                $originalPath = $image->image_path;
                $newPath      = $originalPath;

                if (Storage::disk('public')->exists($originalPath)) {
                    // بناء مسار الملف الجديد في نفس المجلد بامتداد وصيغة مختلفة
                    $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                    $directory = pathinfo($originalPath, PATHINFO_DIRNAME);
                    $newPath   = $directory . '/' . Str::uuid() . '.' . $extension;

                    Storage::disk('public')->copy($originalPath, $newPath);
                }

                $newProduct->images()->create([
                    'image_path' => $newPath,
                    'is_primary' => $image->is_primary,
                    'sort_order' => $image->sort_order,
                ]);
            }

            // 7. تكرار المواصفات الفنية (مع الحفاظ على جميع الترجمات)
            foreach ($original->specifications as $spec) {
                $newProduct->specifications()->create([
                    'group_name' => $spec->getRawOriginal('group_name'),
                    'spec_key'   => $spec->getRawOriginal('spec_key'),
                    'spec_value' => $spec->getRawOriginal('spec_value'),
                ]);
            }

            // 8. تكرار المميزات (مع الحفاظ على جميع الترجمات)
            foreach ($original->features as $feature) {
                $newProduct->features()->create([
                    'feature_text' => $feature->getRawOriginal('feature_text'),
                    'sort_order'   => $feature->sort_order,
                ]);
            }

            // 9. نسخ علاقة قيم الخصائص (Many-to-Many)
            $attributeValueIds = $original->attributeValues->pluck('id')->toArray();
            if (!empty($attributeValueIds)) {
                $newProduct->attributeValues()->sync($attributeValueIds);
            }

            return $newProduct;
        });
    }
}
