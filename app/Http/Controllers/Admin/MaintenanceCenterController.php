<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Http\Resources\MaintenanceCenterResource;
use App\Traits\ManagesSortOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceCenterController extends Controller
{
    use ManagesSortOrder;

    public function index(Request $request)
    {
        $query = MaintenanceCenter::query()->orderBy('sort_order', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

        if ($request->filled('phone')) {
            $query->where('phone', $request->phone);
        }

        return MaintenanceCenterResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|array',
            'name.ar'       => 'required|string',
            'name.en'       => 'nullable|string',
            'name.ku'       => 'nullable|string',
            'city'          => 'required|array',
            'city.ar'       => 'required|string',
            'city.en'       => 'nullable|string',
            'city.ku'       => 'nullable|string',
            'phone'         => 'required|array',
            'phone.*'       => 'required|string',
            'address'       => 'required|array',
            'address.ar'    => 'required|string',
            'address.en'    => 'nullable|string',
            'address.ku'    => 'nullable|string',
            'location_link' => 'nullable|url',
            'sort_order'    => 'nullable|integer|min:1',
        ]);

        $center = DB::transaction(function () use ($request) {
            $data              = $request->all();
            $data['sort_order'] = $this->adjustSortOrder(
                null,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                MaintenanceCenter::query()
            );

            return MaintenanceCenter::create($data);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة المركز بنجاح',
            'data'    => new MaintenanceCenterResource($center),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'sometimes|array',
            'name.ar'       => 'sometimes|string',
            'name.en'       => 'nullable|string',
            'name.ku'       => 'nullable|string',
            'city'          => 'sometimes|array',
            'city.ar'       => 'sometimes|string',
            'city.en'       => 'nullable|string',
            'city.ku'       => 'nullable|string',
            'phone'         => 'sometimes|array',
            'phone.*'       => 'sometimes|string',
            'address'       => 'sometimes|array',
            'address.ar'    => 'sometimes|string',
            'address.en'    => 'nullable|string',
            'address.ku'    => 'nullable|string',
            'location_link' => 'nullable|url',
            'sort_order'    => 'nullable|integer|min:1',
        ]);

        $center = DB::transaction(function () use ($request, $id) {
            $center            = MaintenanceCenter::findOrFail($id);
            $data              = $request->all();
            $data['sort_order'] = $this->adjustSortOrder(
                $center->id,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                MaintenanceCenter::query()
            );

            $center->update($data);
            return $center->fresh();
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات المركز بنجاح',
            'data'    => new MaintenanceCenterResource($center),
        ]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $center       = MaintenanceCenter::findOrFail($id);
            $deletedOrder = (int) $center->sort_order;

            $center->delete();

            $this->reorderAfterDelete(MaintenanceCenter::query(), $deletedOrder);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المركز بنجاح',
        ]);
    }

    /**
     * إصلاح الترتيب الكامل لمراكز الدعم (يُعالج البيانات التالفة)
     */
    public function normalize()
    {
        $this->normalizeOrder(MaintenanceCenter::query());

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة ترتيب المراكز بنجاح',
        ]);
    }
}