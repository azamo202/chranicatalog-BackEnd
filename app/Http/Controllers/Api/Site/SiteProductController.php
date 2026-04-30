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

        // 2. الفلترة حسب القسم (عبر الرابط)
        if ($request->filled('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // 3. الفلترة حسب الماركة
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // 4. الفلترة حسب السعر
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
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
        if ($request->filled('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

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
            ->with(['category', 'brand', 'images', 'specifications', 'features'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new ProductResource($product)
        ]);
    }
}