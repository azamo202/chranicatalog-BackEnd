<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportVideo;
use App\Http\Resources\SupportVideoResource;
use Illuminate\Http\Request;

class SupportVideoController extends Controller
{
    public function index(Request $request)
    {
        // 1. بدء الاستعلام
        $query = SupportVideo::query();

        // 2. الفلترة (البحث في عنوان الفيديو بجميع اللغات)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        // 3. جلب البيانات
        return SupportVideoResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'title.en' => 'nullable|string',
            'title.ku' => 'nullable|string',
            'youtube_url' => 'required|url',
        ]);

        $video = SupportVideo::create($request->all());
        return response()->json(['status' => true, 'message' => 'تم إضافة الفيديو بنجاح', 'data' => new SupportVideoResource($video)], 201);
    }

    public function update(Request $request, $id)
    {
        $video = SupportVideo::findOrFail($id);
        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'youtube_url' => 'required|url',
        ]);

        $video->update($request->all());
        return response()->json(['status' => true, 'message' => 'تم تحديث الفيديو بنجاح', 'data' => new SupportVideoResource($video)]);
    }

    public function destroy($id)
    {
        SupportVideo::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف الفيديو بنجاح']);
    }
}