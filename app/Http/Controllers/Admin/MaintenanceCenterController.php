<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Http\Resources\MaintenanceCenterResource;
use Illuminate\Http\Request;

class MaintenanceCenterController extends Controller
{
    public function index()
    {
        return MaintenanceCenterResource::collection(MaintenanceCenter::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'nullable|string',
            'name.ku' => 'nullable|string',
            'phone' => 'required|string',
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

            'phone' => 'sometimes|string',

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
