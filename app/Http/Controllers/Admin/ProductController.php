<?php

namespace App\Http\Controllers\Admin; // تأكد أن المسار Api وليس Admin إذا كنت تضعه في مجلد Api

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        // 3. الفلترة حسب الماركة (Brand)
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // 4. الفلترة حسب السعر (أقل سعر وأعلى سعر)
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
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

        // 6. الترتيب (الأحدث، أو الأرخص، أو الأغلى)
        if ($request->filled('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest(); // الافتراضي
            }
        } else {
            $query->latest();
        }

        // 7. جلب البيانات مع التقسيم (Pagination)
        $products = $query->get();
        return ProductResource::collection($products);
    }
    /**
     * عرض تفاصيل منتج واحد (لصفحة المنتج في Next.js)
     */
    public function show($id, $slug = null)
    {
        // نبحث بالـ ID لأنه الأسرع (Primary Key)
        $product = Product::where('id', $id)
            ->with(['category', 'brand', 'images', 'specifications', 'features', 'attributeValues.attribute'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new ProductResource($product)
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
            'price' => 'nullable|numeric|min:0', // التحقق من السعر
            'images.*' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // توليد الـ Slug بناءً على الإنجليزي أو العربي
            $slugName = $request->name['en'] ?? $request->name['ar'];

            // 2. إنشاء المنتج الأساسي
            $product = Product::create([
                'name' => $request->name, // لارافل ستحفظ المصفوفة كـ JSON تلقائياً بفضل الحزمة
                'slug' => Str::slug($slugName) . '-' . uniqid(),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'model_number' => $request->model_number,
                'origin_country' => $request->origin_country,
                'price' => $request->price, // حفظ السعر
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            // 3. معالجة الصور
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $index === 0 ? true : false,
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
            'price' => 'nullable|numeric|min:0',
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
                'price',
                'is_active',
                'name',
                'description'
            ]);

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
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => false,
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
     * حذف المنتج (للوحة التحكم)
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف المنتج بنجاح'
        ]);
    }
}
