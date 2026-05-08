<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class SiteProductController extends Controller
{
    /**
     * عرض قائمة المنتجات لزوار الموقع (مع الفلترة المتقدمة والبحث)
     */
    public function index(Request $request)
    {
        // 1. الاستعلام الأساسي: جلب المنتجات الفعالة فقط مع صورتها الرئيسية والقسم والماركة
        $query = Product::where('is_active', true)
            ->with(['category', 'brand', 'images' => function ($q) {
                $q->where('is_primary', true); 
            }]);

        // 2. الفلترة حسب القسم (يدعم التصنيفات الفرعية والقيم المتعددة)
        if ($request->filled('category_slug')) {
            $slugs = is_array($request->category_slug) 
                ? $request->category_slug 
                : explode(',', $request->category_slug);

            $categoryIds = \App\Models\Category::whereIn('slug', $slugs)
                ->get()
                ->flatMap(function($category) {
                    // جلب معرف القسم الحالي + جميع معرفات الأقسام الفرعية التابعة له
                    return [$category->id, ...$category->children()->pluck('id')->toArray()];
                })
                ->unique();

            $query->whereIn('category_id', $categoryIds);
        }

        // 3. الفلترة حسب الماركة
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // 5. البحث النصي الذكي (يبحث في اللغات الثلاث وفي رقم الموديل)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name->ar', 'LIKE', "%{$search}%")
                  ->orWhere('name->en', 'LIKE', "%{$search}%")
                  ->orWhere('name->ku', 'LIKE', "%{$search}%")
                  ->orWhere('origin_country->ar', 'LIKE', "%{$search}%")
                  ->orWhere('origin_country->en', 'LIKE', "%{$search}%")
                  ->orWhere('origin_country->ku', 'LIKE', "%{$search}%")
                  ->orWhere('model_number', 'LIKE', "%{$search}%");
            });
        }

        // 6. الترتيب
        $query->latest();

        // 7. التقسيم (Pagination)
        $products = $query->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * عرض تفاصيل منتج واحد لزوار الموقع (يدعم جلب المواصفات والمميزات)
     */
    public function show($id, $slug = null)
    {
        // البحث باستخدام الـ ID لسرعة الأداء والتأكد من أن المنتج فعال
        $product = Product::where('id', $id)
            ->where('is_active', true)
            ->with(['category.parent', 'brand', 'images', 'specifications', 'features'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * مقارنة بين عدة منتجات (عن طريق تمرير مصفوفة من المعرفات)
     */
    public function compare(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:2|max:4', // السماح بمقارنة 2 إلى 4 منتجات
            'ids.*' => 'integer|exists:products,id'
        ]);

        $products = Product::whereIn('id', $request->ids)
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'specifications', 'features'])
            ->get();

        // ترتيب المنتجات بنفس الترتيب الممرر في الطلب (اختياري، لكنه مفيد لتجربة المستخدم)
        $sortedProducts = collect($request->ids)->map(function ($id) use ($products) {
            return $products->firstWhere('id', $id);
        })->filter();

        return response()->json([
            'status' => true,
            'data' => ProductResource::collection($sortedProducts)
        ]);
    }
}