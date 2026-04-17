<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * عرض جميع الأقسام (الرئيسية مع فروعها)
     */
    public function index()
    {
        // جلب الأقسام الرئيسية فقط، مع تحميل الأقسام الفرعية التابعة لها
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return response()->json([
            'status' => true,
            'data' => CategoryResource::collection($categories)
        ], 200);
    }

    /**
     * إنشاء قسم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'parent_id', 'is_active']);

        $slugName = $request->name['en'] ?? $request->name['ar'];
        $data['slug'] = Str::slug($slugName);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة القسم بنجاح', // تم التصحيح هنا
            'data' => new CategoryResource($category)
        ], 201);
    }
    /**
     * عرض قسم واحد بناءً على المعرف أو الـ Slug
     */
    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new CategoryResource($category)
        ], 200);
    }

    /**
     * تحديث بيانات القسم
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'parent_id', 'is_active']);

        $slugName = $request->name['en'] ?? $request->name['ar'];
        $data['slug'] = Str::slug($slugName);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        // تم التصحيح هنا: استخدام update بدلاً من create
        $category->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث القسم بنجاح', // تم التصحيح هنا
            'data' => new CategoryResource($category)
        ], 200); // تم التصحيح هنا إلى 200 بدلاً من 201
    }
    /**
     * حذف القسم
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // عند استخدام cascade في الـ migration سيتم حذف الأقسام الفرعية تلقائياً
        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف القسم بنجاح'
        ], 200);
    }
}
