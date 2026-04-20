<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;

class SiteBrandController extends Controller
{
    /**
     * عرض جميع الماركات لزوار الموقع
     */
    public function index()
    {
        // جلب جميع الماركات
        $brands = Brand::all();

        return response()->json([
            'status' => true,
            'data' => BrandResource::collection($brands)
        ]);
    }
}