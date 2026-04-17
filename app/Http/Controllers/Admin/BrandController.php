<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * عرض جميع العلامات التجارية
     */
    public function index()
    {
        $brands = Brand::all();
        
        return response()->json([
            'status' => true,
            'data' => BrandResource::collection($brands)
        ], 200);
    }

    /**
     * إنشاء علامة تجارية جديدة (للوحة التحكم)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only('name');

        // معالجة رفع الشعار
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة العلامة التجارية بنجاح',
            'data' => new BrandResource($brand)
        ], 201);
    }

    /**
     * عرض علامة تجارية واحدة
     */
    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        
        return response()->json([
            'status' => true,
            'data' => new BrandResource($brand)
        ], 200);
    }

    /**
     * تحديث بيانات العلامة التجارية
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only('name');

        // التحقق من وجود شعار جديد مرفوع
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث العلامة التجارية بنجاح',
            'data' => new BrandResource($brand)
        ], 200);
    }

    /**
     * حذف العلامة التجارية
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف العلامة التجارية بنجاح'
        ], 200);
    }
}