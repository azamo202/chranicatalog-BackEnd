<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportVideo;
use App\Http\Resources\SupportVideoResource;
use Illuminate\Http\Request;
use App\Services\YoutubeService;
use App\Rules\ValidYoutube;

class SupportVideoController extends Controller
{
    public function index(Request $request)
    {
        // 1. بدء الاستعلام
        $query = SupportVideo::query()->orderBy('sort_order', 'asc');

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
            'youtube_url' => ['required', 'string', new ValidYoutube],
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['youtube_id'] = YoutubeService::extractId($request->youtube_url);
        unset($data['youtube_url']);
        $data['sort_order'] = $this->adjustSortOrder(null, $data['sort_order'] ?? null, SupportVideo::query());

        $video = SupportVideo::create($data);
        return response()->json(['status' => true, 'message' => 'تم إضافة الفيديو بنجاح', 'data' => new SupportVideoResource($video)], 201);
    }

    public function update(Request $request, $id)
    {
        $video = SupportVideo::findOrFail($id);
        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'youtube_url' => ['required', 'string', new ValidYoutube],
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['youtube_id'] = YoutubeService::extractId($request->youtube_url);
        unset($data['youtube_url']);
        $data['sort_order'] = $this->adjustSortOrder($video->id, $data['sort_order'] ?? null, SupportVideo::query());

        $video->update($data);
        return response()->json(['status' => true, 'message' => 'تم تحديث الفيديو بنجاح', 'data' => new SupportVideoResource($video)]);
    }

    private function adjustSortOrder($modelId, $newOrder, $query)
    {
        if (empty($newOrder) || $newOrder <= 0) {
            $max = (clone $query)->max('sort_order') ?? 0;
            return $max + 1;
        }

        $oldOrder = null;
        if ($modelId) {
            $oldOrder = (clone $query)->where('id', $modelId)->value('sort_order');
        }

        if ($oldOrder !== null) {
            $oldOrder = (int)$oldOrder;
            $newOrder = (int)$newOrder;

            if ($newOrder === $oldOrder) {
                return $newOrder;
            }

            if ($newOrder < $oldOrder) {
                // Moving up: Shift intermediate items down (increment)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->where('sort_order', '>=', $newOrder)
                    ->where('sort_order', '<', $oldOrder)
                    ->increment('sort_order');
            } else {
                // Moving down: Shift intermediate items up (decrement)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->where('sort_order', '>', $oldOrder)
                    ->where('sort_order', '<=', $newOrder)
                    ->decrement('sort_order');
            }
        } else {
            // New item: Shift all items starting from newOrder up
            $exists = (clone $query)->where('sort_order', $newOrder)->exists();
            if ($exists) {
                (clone $query)
                    ->where('sort_order', '>=', $newOrder)
                    ->increment('sort_order');
            }
        }

        return $newOrder;
    }

    public function destroy($id)
    {
        SupportVideo::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف الفيديو بنجاح']);
    }
}