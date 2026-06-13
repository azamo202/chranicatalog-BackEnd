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
        $query = MaintenanceCenter::query()->orderBy('sort_order', 'asc');

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
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['sort_order'] = $this->adjustSortOrder(null, $data['sort_order'] ?? null, MaintenanceCenter::query());

        $center = MaintenanceCenter::create($data);
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
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['sort_order'] = $this->adjustSortOrder($center->id, $data['sort_order'] ?? null, MaintenanceCenter::query());

        $center->update($data);
        return response()->json(['status' => true, 'message' => 'تم تحديث بيانات المركز بنجاح', 'data' => new MaintenanceCenterResource($center)]);
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

    public function destroy($id)
    {
        MaintenanceCenter::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف المركز بنجاح']);
    }
}