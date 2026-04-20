<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\MaintenanceCenter;
use App\Models\SupportVideo;
use App\Models\SupportDownload;
use Carbon\Carbon; // تأكد من استدعاء Carbon
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * جلب إحصائيات لوحة التحكم السريعة
     */
    public function index()
    {
        // 1. تجهيز بيانات الرسم البياني الدائري (توزيع الأقسام)
        $categoryDistribution = Category::withCount('products')
            ->having('products_count', '>', 0) // جلب الأقسام التي تحتوي على منتجات فقط
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name, 
                    'value' => $category->products_count,
                ];
            });

        // 2. تجهيز بيانات الرسم البياني الشريطي (المنتجات المضافة في آخر 6 أشهر)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M'), // (Jan, Feb, Mar...)
                'products' => Product::whereMonth('created_at', $date->month)
                                     ->whereYear('created_at', $date->year)
                                     ->count()
            ];
        }

        // جمع الأرقام من كافة الجداول (تم تعديل المفاتيح لتطابق الفرونت اند)
        $stats = [
            'totalProducts' => Product::count(),
            'totalCategories' => Category::count(),
            'totalBrands' => Brand::count(),
            'hiddenProducts' => Product::where('is_active', false)->count(), // تم التعديل لتناسب (المنتجات المخفية)
            
            // بيانات الرسوم البيانية الجديدة
            'monthlyData' => $monthlyData,
            'categoryDistribution' => $categoryDistribution,

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