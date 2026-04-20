<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class SiteCategoryController extends Controller
{
    /**
     * عرض جميع الأقسام الرئيسية والفرعية الفعالة لزوار الموقع
     */
    public function index()
    {
        // جلب الأقسام الرئيسية فقط (parent_id = null) الفعالة
        // مع جلب أبنائها (الأقسام الفرعية) الفعالة أيضاً
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return response()->json([
            'status' => true,
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * عرض تفاصيل قسم واحد (اختياري، إذا كان لديك صفحة خاصة بتفاصيل القسم)
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new CategoryResource($category)
        ]);
    }
}