<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Http\Resources\MaintenanceCenterResource;
use Illuminate\Http\Request;

class MaintenanceCenterController extends Controller
{
    public function index(Request $request)
    {
        // 1. بدء الاستعلام
        $query = MaintenanceCenter::query();

        // 2. الفلترة العامة (البحث الشامل في الاسم، العنوان بجميع اللغات، أو رقم الهاتف)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name->ar', 'LIKE', "%{$search}%")
                  ->orWhere('name->en', 'LIKE', "%{$search}%")
                  ->orWhere('name->ku', 'LIKE', "%{$search}%")
                  ->orWhere('city->ar', 'LIKE', "%{$search}%")
                  ->orWhere('city->en', 'LIKE', "%{$search}%")
                  ->orWhere('city->ku', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('address->ar', 'LIKE', "%{$search}%")
                  ->orWhere('address->en', 'LIKE', "%{$search}%")
                  ->orWhere('address->ku', 'LIKE', "%{$search}%");
            });
        }

        // 3. الفلترة المخصصة (مثال: إذا أردت جلب مراكز برقم هاتف محدد فقط)
        if ($request->filled('phone')) {
            $query->where('phone', $request->phone);
        }

        // 4. جلب البيانات بعد تطبيق الفلاتر
        return MaintenanceCenterResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',
            'city' => 'required|array',
            'city.ar' => 'required|string',
            'city.en' => 'nullable|string',
            'city.ku' => 'nullable|string',
            'phone' => 'required|array',
            'phone.*' => 'required|string',
            'address' => 'required|array',
            'address.ar' => 'required|string',
            'address.en' => 'nullable|string',
            'address.ku' => 'nullable|string',
            'location_link' => 'nullable|url',
        ]);

        $center = MaintenanceCenter::create($request->all());
        return response()->json(['status' => true, 'message' => 'تم إضافة المركز بنجاح', 'data' => new MaintenanceCenterResource($center)], 201);
    }

    public function update(Request $request, $id)
    {
        $center = MaintenanceCenter::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|array',
            'name.ar' => 'sometimes|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',

            'city' => 'sometimes|array',
            'city.ar' => 'sometimes|string',
            'city.en' => 'nullable|string',
            'city.ku' => 'nullable|string',

            'phone' => 'sometimes|array',
            'phone.*' => 'sometimes|string',

            'address' => 'sometimes|array',
            'address.ar' => 'sometimes|string',
            'address.en' => 'nullable|string',
            'address.ku' => 'nullable|string',

            'location_link' => 'nullable|url',
        ]);
        $center->update($request->all());
        return response()->json(['status' => true, 'message' => 'تم تحديث بيانات المركز بنجاح', 'data' => new MaintenanceCenterResource($center)]);
    }

    public function destroy($id)
    {
        MaintenanceCenter::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف المركز بنجاح']);
    }
}