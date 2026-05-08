<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\MaintenanceCenterController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\SupportDownloadController;
use App\Http\Controllers\Admin\SupportVideoController;
use App\Http\Controllers\Api\Site\SiteBrandController;
use App\Http\Controllers\Api\Site\SiteCategoryController;
use App\Http\Controllers\Api\Site\SiteProductController;
use App\Http\Controllers\Api\Site\SiteSupportController;
use App\Http\Controllers\Api\FrontHomepageController;
use App\Http\Middleware\CheckSuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// =========================================================
// مسارات الموقع العام (Frontend Website) - للزوار بدون تسجيل دخول
// =========================================================
Route::prefix('site')->group(function () {

    // مسار الصفحة الرئيسية (جلب الأقسام الديناميكية مع منتجاتها)
    Route::get('/home-sections', [FrontHomepageController::class, 'getActiveSections']);

    // مسارات المنتجات الخاصة بالكتالوج
    Route::get('/products/compare', [SiteProductController::class, 'compare']);
    Route::get('/products', [SiteProductController::class, 'index']);
    Route::get('/products/{id}/{slug?}', [SiteProductController::class, 'show']);

    // مسارات الأقسام
    Route::get('/categories', [SiteCategoryController::class, 'index']);
    Route::get('/categories/{slug}', [SiteCategoryController::class, 'show']);

    // مسارات الماركات
    Route::get('/brands', [SiteBrandController::class, 'index']);

    Route::get('/maintenance-centers', [SiteSupportController::class, 'maintenanceCenters']);
    Route::get('/videos', [SiteSupportController::class, 'videos']);
    Route::get('/downloads', [SiteSupportController::class, 'downloads']);
    Route::get('/store-settings', [StoreSettingController::class, 'index']);
    Route::post('/contact', [\App\Http\Controllers\Api\Site\SiteContactController::class, 'send']);
});











// ---------------------------------------------------------
// المسارات العامة (Public Routes) - للواجهة الأمامية للموقع
// ---------------------------------------------------------
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// مسار تسجيل الدخول (لا يحتاج إلى حماية لأنه نقطة الدخول)
Route::post('/admin/login', [AdminController::class, 'login']);
// جلب قائمة المنتجات (مع دعم الـ Pagination)
Route::get('/products', [ProductController::class, 'index']);
// جلب منتج واحد بالـ Slug (وليس بالـ ID ليكون أفضل للـ Next.js والـ SEO)
Route::get('/products/{id}/{slug?}', [ProductController::class, 'show']);
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);

Route::get('/maintenance-centers', [MaintenanceCenterController::class, 'index']);
Route::get('/support-videos', [SupportVideoController::class, 'index']);
Route::get('/support-downloads', [SupportDownloadController::class, 'index']);

// المسارات المحمية (تتطلب وجود Token صالح)
Route::middleware('auth:sanctum')->group(function () {
    // مسار إنشاء مدير جديد (محمي بصلاحية Super Admin فقط)
    Route::post('/admin/users', [AdminController::class, 'store'])
        ->middleware(CheckSuperAdmin::class);
    Route::get('/admin/profile', [AdminController::class, 'profile']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
    Route::get('/admin/users', [AdminController::class, 'index']);
    Route::get('/admin/users/{id}', [AdminController::class, 'show']);
    Route::put('/admin/users/{id}', [AdminController::class, 'update'])->middleware(CheckSuperAdmin::class);
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroy'])->middleware(CheckSuperAdmin::class);


    // مسارات إدارة الأقسام (إضافة، تعديل، حذف)
    Route::post('/categories', [CategoryController::class, 'store']);
    // نستخدم POST مع تمرير _method=PUT في الـ FormData لدعم رفع الملفات في Laravel
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products', [ProductController::class, 'store']);
    // نستخدم POST مع تمرير _method=PUT في الـ FormData لدعم رفع الصور
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // مسارات إدارة العلامات التجارية
    Route::post('/brands', [BrandController::class, 'store']);
    // تذكر: عند التعديل ورفع صورة من الواجهة نستخدم POST مع _method=PUT
    Route::post('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
    Route::post('/maintenance-centers', [MaintenanceCenterController::class, 'store']);
    Route::post('/maintenance-centers/{id}', [MaintenanceCenterController::class, 'update']);
    Route::delete('/maintenance-centers/{id}', [MaintenanceCenterController::class, 'destroy']);

    Route::post('/support-videos', [SupportVideoController::class, 'store']);
    Route::post('/support-videos', [SupportVideoController::class, 'store']);
    Route::post('/support-videos/{id}', [SupportVideoController::class, 'update']);
    Route::delete('/support-videos/{id}', [SupportVideoController::class, 'destroy']);

    Route::post('/support-downloads', [SupportDownloadController::class, 'store']);
    Route::put('/support-downloads/{id}', [SupportDownloadController::class, 'update']);
    Route::delete('/support-downloads/{id}', [SupportDownloadController::class, 'destroy']);
    Route::get('/admin/dashboard-stats', [DashboardController::class, 'index']);

    // مسارات إدارة أقسام الصفحة الرئيسية (Homepage Sections)
    Route::get('/homepage-sections', [HomepageSectionController::class, 'index']);
    Route::post('/homepage-sections', [HomepageSectionController::class, 'store']);
    Route::get('/homepage-sections/{id}', [HomepageSectionController::class, 'show']);
    Route::put('/homepage-sections/{id}', [HomepageSectionController::class, 'update']);
    Route::delete('/homepage-sections/{id}', [HomepageSectionController::class, 'destroy']);
    Route::post('/homepage-sections/{id}/products/attach', [HomepageSectionController::class, 'attachProducts']);
    Route::post('/homepage-sections/{id}/products/detach', [HomepageSectionController::class, 'detachProducts']);

    // مسارات إعدادات المتجر (Store Settings)
    Route::get('/store-settings', [StoreSettingController::class, 'index']);
    Route::post('/store-settings', [StoreSettingController::class, 'update']);
});
