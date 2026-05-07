<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Models\SupportVideo;
use App\Models\SupportDownload;
use App\Http\Resources\MaintenanceCenterResource;
use App\Http\Resources\SupportVideoResource;
use App\Http\Resources\SupportDownloadResource;
use Illuminate\Http\Request;

class SiteSupportController extends Controller
{
    /**
     * جلب جميع مراكز الصيانة
     */
    public function maintenanceCenters(Request $request)
    {
        $query = MaintenanceCenter::query();

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

        $centers = $query->latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => MaintenanceCenterResource::collection($centers)
        ]);
    }

    /**
     * جلب جميع الفيديوهات التعليمية
     */
    public function videos(Request $request)
    {
        $query = SupportVideo::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        $videos = $query->latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => SupportVideoResource::collection($videos)
        ]);
    }

    /**
     * جلب جميع ملفات التحميل (أدلة الاستخدام)
     */
    public function downloads(Request $request)
    {
        $query = SupportDownload::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        $downloads = $query->latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => SupportDownloadResource::collection($downloads)
        ]);
    }
}