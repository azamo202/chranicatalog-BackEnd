<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Middleware\CheckSuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


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



// المسارات المحمية (تتطلب وجود Token صالح)
Route::middleware('auth:sanctum')->group(function () {
    // مسار إنشاء مدير جديد (محمي بصلاحية Super Admin فقط)
    Route::post('/admin/users', [AdminController::class, 'store'])
         ->middleware(CheckSuperAdmin::class);
    Route::get('/admin/profile', [AdminController::class, 'profile']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
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
});
