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
    public function maintenanceCenters()
    {
        $centers = MaintenanceCenter::latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => MaintenanceCenterResource::collection($centers)
        ]);
    }

    /**
     * جلب جميع الفيديوهات التعليمية
     */
    public function videos()
    {
        $videos = SupportVideo::latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => SupportVideoResource::collection($videos)
        ]);
    }

    /**
     * جلب جميع ملفات التحميل (أدلة الاستخدام)
     */
    public function downloads()
    {
        $downloads = SupportDownload::latest()->get();
        
        return response()->json([
            'status' => true,
            'data' => SupportDownloadResource::collection($downloads)
        ]);
    }
}