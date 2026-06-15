<?php

namespace App\Http\Controllers\Admin; // تأكد أن المسار Api وليس Admin إذا كنت تضعه في مجلد Api

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * عرض قائمة المنتجات للواجهة (مع فلترة متقدمة وبحث)
     */
    public function index(Request $request)
    {
        // 1. الاستعلام الأساسي
        $query = Product::query()
            ->with(['category', 'brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }]);

        // 2. الفلترة حسب القسم (عن طريق الـ Slug لأنه يأتي من الرابط عادة)
        if ($request->filled('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }
        
        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            
            // جلب معرف القسم الحالي مع جميع معرفات الأقسام الفرعية التابعة له
            $categoryIds = \App\Models\Category::where('id', $categoryId)
                ->get()
                ->flatMap(function($category) {
                    return [$category->id, ...$category->children()->pluck('id')->toArray()];
                })
                ->unique();
                
            $query->whereIn('category_id', $categoryIds);
        }

        // 3. الفلترة حسب الماركة (Brand)
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // 5. البحث النصي المتقدم (في الاسم باللغات الثلاث)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // البحث داخل حقول الـ JSON للغات
                $q->where('name->ar', 'LIKE', "%{$search}%")
                    ->orWhere('name->en', 'LIKE', "%{$search}%")
                    ->orWhere('name->ku', 'LIKE', "%{$search}%")
                    ->orWhere('origin_country->ar', 'LIKE', "%{$search}%")
                    ->orWhere('origin_country->en', 'LIKE', "%{$search}%")
                    ->orWhere('origin_country->ku', 'LIKE', "%{$search}%")
                    ->orWhere('model_number', 'LIKE', "%{$search}%"); // البحث برقم الموديل أيضاً!
            });
        }

        // الفلترة المخصصة برقم الموديل
        if ($request->filled('model_number')) {
            $query->where('model_number', 'LIKE', "%{$request->model_number}%");
        }

        // الفلترة حسب حالة المنتج (معروض / مخفي)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // 6. الترتيب (الترتيب حسب القسم إن وجد، وإلا الأحدث)
        if ($request->filled('category_slug') || $request->filled('category_id')) {
            $query->orderBy('sort_order', 'asc')->latest();
        } else {
            $query->latest();
        }

        // 7. جلب البيانات مع التقسيم (Pagination)
        $perPage = $request->input('per_page', 50); // قراءة عدد العناصر في الصفحة أو استخدام 50 كافتراضي
        $products = $query->paginate($perPage);
        return ProductResource::collection($products);
    }
    /**
     * عرض تفاصيل منتج واحد (لصفحة المنتج في Next.js)
     */
       /**
     * عرض تفاصيل منتج واحد (لصفحة المنتج في Next.js / لوحة التحكم)
     */
    public function show($id, $slug = null)
    {
        // نبحث بالـ ID لأنه الأسرع (Primary Key)
        $product = Product::where('id', $id)
            ->with(['category', 'brand', 'images', 'specifications', 'features', 'attributeValues.attribute'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            // التعديل هنا: إرجاع المنتج مباشرة بدون ProductResource
            // لكي تستلم لوحة التحكم (React) المصفوفات والتراجم بشكلها الأصلي
            // وتتمكن من وضعها في حقول التعديل بشكل صحيح
            'data' => $product
        ]);
    }


    /**
     * إضافة منتج جديد (للوحة التحكم)
     * ملاحظة: يجب إرسال البيانات كـ FormData لأنها تحتوي على صور ومصفوفات
     */
    public function store(Request $request)
    {
        // 1. تحديث التحقق (Validation) ليدعم المصفوفات واللغات الثلاث والسعر
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string', // الاسم العربي إجباري
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string', // إضافة الكردية

            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'description.ku' => 'nullable|string', // إضافة الكردية للوصف

            'origin_country' => 'nullable|array',
            'origin_country.ar' => 'nullable|string',
            'origin_country.en' => 'nullable|string',
            'origin_country.ku' => 'nullable|string',

            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'images.*' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // توليد الـ Slug بناءً على الإنجليزي أو العربي
            $slugName = $request->name['en'] ?? $request->name['ar'];

            // 2. إنشاء المنتج الأساسي
            $sortOrder = Product::where('category_id', $request->category_id)->max('sort_order');
            $sortOrder = $sortOrder !== null ? $sortOrder + 1 : 1;

            $product = Product::create([
                'name' => $request->name, // لارافل ستحفظ المصفوفة كـ JSON تلقائياً بفضل الحزمة
                'slug' => Str::slug($slugName) . '-' . uniqid(),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'model_number' => $request->model_number,
                'origin_country' => $request->origin_country,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $sortOrder,
            ]);

            // 3. معالجة الصور
            $primaryIndex = $request->input('primary_image_index', 0);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => (int)$primaryIndex === $index,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            // 4. إضافة المواصفات (ستستقبل اللغات كـ JSON)
            if ($request->has('specifications')) {
                $specs = json_decode($request->specifications, true);
                if (is_array($specs)) {
                    $product->specifications()->createMany($specs);
                }
            }

            // 5. إضافة المميزات النقطية
            if ($request->has('features')) {
                $features = json_decode($request->features, true);
                if (is_array($features)) {
                    $product->features()->createMany($features);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم حفظ المنتج بنجاح',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث بيانات المنتج (متعدد اللغات + السعر)
     * ملاحظة: يجب إرسال الطلب كـ POST مع إرفاق _method=PUT لدعم رفع الصور
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. تحديث التحقق ليقبل المصفوفات (اللغات الثلاث) وحقل السعر
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',

            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'description.ku' => 'nullable|string',

            'origin_country' => 'nullable|array',
            'origin_country.ar' => 'nullable|string',
            'origin_country.en' => 'nullable|string',
            'origin_country.ku' => 'nullable|string',

            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'images.*' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // 2. تحديث البيانات الأساسية
            $data = $request->only([
                'category_id',
                'brand_id',
                'model_number',
                'origin_country',
                'is_active',
                'name',
                'description'
            ]);

            // إذا تغير القسم، نقوم بإسناد ترتيب جديد للمنتج في القسم الجديد ليتفادى التعارض
            if (isset($data['category_id']) && (int)$data['category_id'] !== (int)$product->category_id) {
                $maxSort = Product::where('category_id', $data['category_id'])->max('sort_order');
                $data['sort_order'] = $maxSort !== null ? $maxSort + 1 : 1;
            }

            // 3. تحديث الـ slug بذكاء (التحقق مما إذا كان الاسم الإنجليزي أو العربي قد تغير)
            $newSlugName = $request->name['en'] ?? $request->name['ar'];

            // نجلب الاسم القديم (الإنجليزي، وإن لم يوجد نجلب العربي) للمقارنة
            $oldSlugName = $product->getTranslation('name', 'en', false) ?: $product->getTranslation('name', 'ar', false);

            if ($newSlugName !== $oldSlugName) {
                $data['slug'] = Str::slug($newSlugName) . '-' . uniqid();
            }

            // تنفيذ التحديث في قاعدة البيانات
            $product->update($data);

            // 4. معالجة الصور الإضافية
            if ($request->hasFile('images')) {
                $primaryIndex = $request->input('primary_image_index');
                
                // إذا تم تحديد صورة جديدة لتكون هي الرئيسية، قم بإلغاء الرئيسية عن الصور القديمة
                if ($primaryIndex !== null && $primaryIndex !== '') {
                    $product->images()->update(['is_primary' => false]);
                }
                
                // إذا لم تكن هناك أي صور للمنتج، نجعل الصورة الأولى المرفوعة هي الأساسية
                if (($primaryIndex === null || $primaryIndex === '') && $product->images()->count() === 0) {
                    $primaryIndex = 0;
                }

                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    $maxSortOrder = $product->images()->max('sort_order') ?? 0;
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => ($primaryIndex !== null && $primaryIndex !== '') ? ((int)$primaryIndex === $index) : false,
                        'sort_order' => $maxSortOrder + 1,
                    ]);
                }
            }

            // 5. تحديث المواصفات الفنية
            if ($request->has('specifications')) {
                $specs = json_decode($request->specifications, true);
                if (is_array($specs)) {
                    $product->specifications()->delete(); // مسح القديم
                    $product->specifications()->createMany($specs); // إدخال الجديد المترجم
                }
            }

            // 6. تحديث المميزات النقطية بنفس الطريقة
            if ($request->has('features')) {
                $features = json_decode($request->features, true);
                if (is_array($features)) {
                    $product->features()->delete();
                    $product->features()->createMany($features); // إدخال الجديد المترجم
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث بيانات المنتج بنجاح',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف المنتج مع إعادة ترتيب المنتجات المتبقية في نفس القسم
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $deletedOrder = (int) $product->sort_order;
        $categoryId   = $product->category_id;

        $product->delete();

        // إعادة ترتيب المنتجات التي كانت بعد المنتج المحذوف (تسلسل بلا فراغات)
        if ($deletedOrder > 0) {
            Product::where('category_id', $categoryId)
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المنتج بنجاح'
        ]);
    }

    /**
     * تعيين صورة كصورة أساسية للمنتج
     */
    public function setPrimaryImage($id)
    {
        $image = ProductImage::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // إلغاء تعيين الصورة الأساسية القديمة للمنتج
            ProductImage::where('product_id', $image->product_id)
                ->update(['is_primary' => false]);
                
            // تعيين هذه الصورة كأساسية
            $image->update(['is_primary' => true]);
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم تعيين الصورة كصورة رئيسية بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف صورة معينة من المنتج
     */
    public function deleteImage($id)
    {
        $image = ProductImage::findOrFail($id);
        $productId = $image->product_id;
        $wasPrimary = $image->is_primary;

        try {
            // حذف الملف الفيزيائي
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // حذف السجل من قاعدة البيانات
            $image->delete();

            // إذا كانت الصورة المحذوفة هي الأساسية، نعين أول صورة متبقية كصورة أساسية
            if ($wasPrimary) {
                $nextImage = ProductImage::where('product_id', $productId)->first();
                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'تم حذف الصورة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * ترتيب صور المنتج
     */
    public function reorderImages(Request $request, $id)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:product_images,id'
        ]);

        $product = Product::findOrFail($id);
        
        DB::beginTransaction();
        try {
            foreach ($request->image_ids as $index => $imageId) {
                // Ensure the image belongs to this product
                ProductImage::where('id', $imageId)
                    ->where('product_id', $product->id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث ترتيب الصور بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث ترتيب المنتج وإعادة ترتيب المنتجات الأخرى إذا لزم الأمر
     */
    public function updateSortOrder(Request $request, $id)
    {
        $request->validate([
            'sort_order' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);
        $newSortOrder = (int)$request->sort_order;
        $categoryId = $product->category_id;
        $oldSortOrder = (int)$product->sort_order;

        if ($oldSortOrder === $newSortOrder) {
            return response()->json(['status' => true, 'message' => 'لم يتم تغيير الترتيب']);
        }

        DB::beginTransaction();
        try {
            // نجلب جميع المنتجات الأخرى في نفس القسم مرتبة
            $otherProducts = Product::where('category_id', $categoryId)
                                   ->where('id', '!=', $product->id)
                                   ->orderBy('sort_order', 'asc')
                                   ->orderBy('id', 'asc')
                                   ->get();

            // ضمان عدم إدخال رقم أكبر من الحد الأقصى للمنتجات لتفادي الفراغات
            $maxPossible = $otherProducts->count() + 1;
            if ($newSortOrder > $maxPossible) {
                $newSortOrder = $maxPossible;
            }

            $counter = 1;
            foreach ($otherProducts as $p) {
                // إذا وصلنا للمكان المطلوب للمنتج الهدف، نترك هذه الخانة فارغة ونزيد العداد
                if ($counter === $newSortOrder) {
                    $counter++;
                }
                
                // تحديث ترتيب المنتجات الأخرى
                if ($p->sort_order !== $counter) {
                    $p->update(['sort_order' => $counter]);
                }
                $counter++;
            }

            // أخيراً نقوم بإعطاء المنتج الهدف رقمه الجديد
            $product->update(['sort_order' => $newSortOrder]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الترتيب بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * إعادة الترتيب التلقائي بشكل متسلسل
     */
    public function autoReorderCategoryProducts($categoryId)
    {
        DB::beginTransaction();
        try {
            $products = Product::where('category_id', $categoryId)
                               ->orderBy('sort_order', 'asc')
                               ->orderBy('id', 'asc')
                               ->get();

            $counter = 1;
            foreach ($products as $product) {
                if ($product->sort_order !== $counter) {
                    $product->update(['sort_order' => $counter]);
                }
                $counter++;
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'تم إعادة الترتيب التلقائي بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
