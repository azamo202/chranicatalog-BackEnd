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
        $categories = Category::whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with('children')
            ->get();

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
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'parent_id', 'is_active', 'sort_order']);
        if (!empty($data['parent_id'])) {
            $data['sort_order'] = 0;
        } else {
            $data['sort_order'] = $this->adjustSortOrder(null, $data['sort_order'] ?? null, Category::whereNull('parent_id'));
        }

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
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'parent_id', 'is_active', 'sort_order']);
        if (!empty($data['parent_id'])) {
            $data['sort_order'] = 0;
        } else {
            $data['sort_order'] = $this->adjustSortOrder($category->id, $data['sort_order'] ?? null, Category::whereNull('parent_id'));
        }

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

    private function adjustSortOrder($modelId, $newOrder, $query)
    {
        if (empty($newOrder) || $newOrder <= 0) {
            $max = (clone $query)->max('sort_order') ?? 0;
            return $max + 1;
        }

        $oldOrder = null;
        if ($modelId) {
            $oldOrder = (clone $query)->where('id', $modelId)->value('sort_order');
        }

        if ($oldOrder !== null) {
            $oldOrder = (int)$oldOrder;
            $newOrder = (int)$newOrder;

            if ($newOrder === $oldOrder) {
                return $newOrder;
            }

            if ($newOrder < $oldOrder) {
                // Moving up: Shift intermediate items down (increment)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->where('sort_order', '>=', $newOrder)
                    ->where('sort_order', '<', $oldOrder)
                    ->increment('sort_order');
            } else {
                // Moving down: Shift intermediate items up (decrement)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->where('sort_order', '>', $oldOrder)
                    ->where('sort_order', '<=', $newOrder)
                    ->decrement('sort_order');
            }
        } else {
            // New item: Shift all items starting from newOrder up
            $exists = (clone $query)->where('sort_order', $newOrder)->exists();
            if ($exists) {
                (clone $query)
                    ->where('sort_order', '>=', $newOrder)
                    ->increment('sort_order');
            }
        }

        return $newOrder;
    }
    /**
     * حذف القسم مع إعادة ترتيب العناصر المتبقية
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $deletedOrder = (int) $category->sort_order;
        $parentId     = $category->parent_id;

        // عند استخدام cascade في الـ migration سيتم حذف الأقسام الفرعية تلقائياً
        $category->delete();

        // إعادة ترتيب العناصر التي كانت بعد العنصر المحذوف (تسلسل بلا فراغات)
        if ($parentId === null && $deletedOrder > 0) {
            Category::whereNull('parent_id')
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف القسم بنجاح'
        ], 200);
    }
}
