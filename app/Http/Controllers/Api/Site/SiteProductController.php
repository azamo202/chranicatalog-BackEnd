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
        $query = Product::select('products.*')
            ->where('products.is_active', true)
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

            $query->whereIn('products.category_id', $categoryIds);
        }

        // 3. الفلترة حسب الماركة
        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }

        // 5. البحث النصي الذكي (يبحث في اللغات الثلاث وفي رقم الموديل)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name->ar', 'LIKE', "%{$search}%")
                  ->orWhere('products.name->en', 'LIKE', "%{$search}%")
                  ->orWhere('products.name->ku', 'LIKE', "%{$search}%")
                  ->orWhere('products.origin_country->ar', 'LIKE', "%{$search}%")
                  ->orWhere('products.origin_country->en', 'LIKE', "%{$search}%")
                  ->orWhere('products.origin_country->ku', 'LIKE', "%{$search}%")
                  ->orWhere('products.model_number', 'LIKE', "%{$search}%");
            });
        }

        // 6. الترتيب
        // نربط جدول الأقسام لكي نرتب بناءً على ترتيب القسم الأب أولاً ثم القسم الفرعي ثم ترتيب المنتج
        $query->leftJoin('categories as c', 'products.category_id', '=', 'c.id')
              ->leftJoin('categories as parent_c', 'c.parent_id', '=', 'parent_c.id')
              // 1. ترتيب القسم الرئيسي (سواء كان الأب أو القسم نفسه)
              ->orderByRaw('COALESCE(parent_c.sort_order, c.sort_order, 9999) asc')
              // 2. تجميع المنتجات بناءً على معرف القسم الرئيسي لمنع تداخل الأقسام التي لها نفس الترتيب
              ->orderByRaw('COALESCE(parent_c.id, c.id) asc')
              // 3. ترتيب الأقسام الفرعية، المنتجات التابعة للقسم الرئيسي تظهر أولاً (-1) ثم الأقسام الفرعية
              ->orderByRaw('CASE WHEN c.parent_id IS NOT NULL THEN COALESCE(c.sort_order, 9999) ELSE -1 END asc')
              // 4. تجميع المنتجات بناءً على معرف القسم الفرعي لمنع تداخل المنتجات
              ->orderBy('c.id', 'asc')
              // 5. أخيراً ترتيب المنتجات داخل كل قسم
              ->orderBy('products.sort_order', 'asc')
              ->latest('products.created_at');

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