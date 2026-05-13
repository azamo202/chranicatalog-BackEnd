<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreSetting;

class StoreSettingController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::first();
        return response()->json([
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'nullable|array',
            'phone.*' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|email',
            'tiktok' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
        ]);

        $settings = StoreSetting::first();

        if ($settings) {
            $settings->update($validatedData);
        } else {
            $settings = StoreSetting::create($validatedData);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings
        ]);
    }
}
