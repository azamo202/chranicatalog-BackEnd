<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\MaintenanceCenter;
use App\Models\SupportVideo;
use App\Models\SupportDownload;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * جلب إحصائيات لوحة التحكم السريعة
     */
    public function index()
    {
        // جمع الأرقام من كافة الجداول
        $stats = [
            'products_count' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'categories_count' => Category::count(),
            'brands_count' => Brand::count(),
            'support' => [
                'maintenance_centers' => MaintenanceCenter::count(),
                'videos' => SupportVideo::count(),
                'downloads' => SupportDownload::count(),
            ],
            // جلب آخر 5 منتجات تم إضافتها للعرض السريع
            'recent_products' => Product::latest()->take(5)->get(['id', 'name', 'created_at']),
        ];

        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }
}