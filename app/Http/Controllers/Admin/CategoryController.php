<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Traits\ManagesSortOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ManagesSortOrder;

    /**
     * عرض جميع الأقسام الرئيسية مع فروعها
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with(['children' => function ($q) {
                $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
            }])
            ->get();

        return response()->json([
            'status' => true,
            'data'   => CategoryResource::collection($categories),
        ], 200);
    }

    /**
     * إنشاء قسم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|array',
            'name.ar'   => 'required|string',
            'name.en'   => 'nullable|string',
            'name.ku'   => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|max:2048',
            'sort_order'=> 'nullable|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = DB::transaction(function () use ($request, $imagePath) {
            $data = $request->only(['name', 'parent_id', 'is_active', 'sort_order']);

            if (!empty($data['parent_id'])) {
                // الأقسام الفرعية لا تملك ترتيباً مُدَاراً
                $data['sort_order'] = 0;
            } else {
                $data['sort_order'] = $this->adjustSortOrder(
                    null,
                    isset($data['sort_order']) ? (int)$data['sort_order'] : null,
                    Category::whereNull('parent_id')
                );
            }

            $slugName    = $request->name['en'] ?? $request->name['ar'];
            $data['slug'] = Str::slug($slugName);

            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            return Category::create($data);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة القسم بنجاح',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    /**
     * عرض قسم واحد
     */
    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => new CategoryResource($category),
        ], 200);
    }

    /**
     * تحديث بيانات القسم
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required|array',
            'name.ar'   => 'required|string',
            'name.en'   => 'nullable|string',
            'name.ku'   => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|max:2048',
            'sort_order'=> 'nullable|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = DB::transaction(function () use ($request, $id, $imagePath) {
            $category = Category::findOrFail($id);
            $data     = $request->only(['name', 'parent_id', 'is_active', 'sort_order']);

            if (!empty($data['parent_id'])) {
                $data['sort_order'] = 0;
            } else {
                $data['sort_order'] = $this->adjustSortOrder(
                    $category->id,
                    isset($data['sort_order']) ? (int)$data['sort_order'] : null,
                    Category::whereNull('parent_id')
                );
            }

            $slugName    = $request->name['en'] ?? $request->name['ar'];
            $data['slug'] = Str::slug($slugName);

            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            $category->update($data);
            return $category->fresh();
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث القسم بنجاح',
            'data'    => new CategoryResource($category),
        ], 200);
    }

    /**
     * حذف القسم مع إعادة ترتيب المتبقين بلا فراغات
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $category     = Category::findOrFail($id);
            $deletedOrder = (int) $category->sort_order;
            $isParent     = is_null($category->parent_id);

            $category->delete(); // cascade يحذف الأقسام الفرعية تلقائياً

            // إعادة الترتيب للأقسام الرئيسية فقط
            if ($isParent && $deletedOrder > 0) {
                $this->reorderAfterDelete(Category::whereNull('parent_id'), $deletedOrder);
            }
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف القسم بنجاح',
        ], 200);
    }

    /**
     * إصلاح الترتيب الكامل للأقسام الرئيسية (يُعالج البيانات التالفة)
     */
    public function normalize()
    {
        $this->normalizeOrder(Category::whereNull('parent_id'));

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة ترتيب الأقسام بنجاح',
        ], 200);
    }
}
