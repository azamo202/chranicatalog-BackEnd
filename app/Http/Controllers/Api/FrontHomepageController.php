<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HomepageSection;

class FrontHomepageController extends Controller
{
    public function getActiveSections()
    {
        $sections = HomepageSection::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->with(['products' => function($query) {
                // optionally load images or other things here
                $query->where('is_active', true)
                      ->with(['category', 'brand', 'images']); 
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sections
        ]);
    }
}
