<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportVideo;
use App\Http\Resources\SupportVideoResource;
use App\Traits\ManagesSortOrder;
use App\Services\YoutubeService;
use App\Rules\ValidYoutube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportVideoController extends Controller
{
    use ManagesSortOrder;

    public function index(Request $request)
    {
        $query = SupportVideo::query()->orderBy('sort_order', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        return SupportVideoResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|array',
            'title.ar'    => 'required|string',
            'title.en'    => 'nullable|string',
            'title.ku'    => 'nullable|string',
            'youtube_url' => ['required', 'string', new ValidYoutube],
            'sort_order'  => 'nullable|integer|min:1',
        ]);

        $video = DB::transaction(function () use ($request) {
            $sortOrder = $this->adjustSortOrder(
                null,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                SupportVideo::query()
            );

            return SupportVideo::create([
                'title'      => $request->title,
                'youtube_id' => YoutubeService::extractId($request->youtube_url),
                'sort_order' => $sortOrder,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة الفيديو بنجاح',
            'data'    => new SupportVideoResource($video),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|array',
            'title.ar'    => 'required|string',
            'title.en'    => 'nullable|string',
            'title.ku'    => 'nullable|string',
            'youtube_url' => ['required', 'string', new ValidYoutube],
            'sort_order'  => 'nullable|integer|min:1',
        ]);

        $video = DB::transaction(function () use ($request, $id) {
            $video     = SupportVideo::findOrFail($id);
            $sortOrder = $this->adjustSortOrder(
                $video->id,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                SupportVideo::query()
            );

            $video->update([
                'title'      => $request->title,
                'youtube_id' => YoutubeService::extractId($request->youtube_url),
                'sort_order' => $sortOrder,
            ]);

            return $video->fresh();
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث الفيديو بنجاح',
            'data'    => new SupportVideoResource($video),
        ]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $video        = SupportVideo::findOrFail($id);
            $deletedOrder = (int) $video->sort_order;

            $video->delete();

            $this->reorderAfterDelete(SupportVideo::query(), $deletedOrder);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الفيديو بنجاح',
        ]);
    }

    /**
     * إصلاح الترتيب الكامل للفيديوهات (يُعالج البيانات التالفة)
     */
    public function normalize()
    {
        $this->normalizeOrder(SupportVideo::query());

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة ترتيب الفيديوهات بنجاح',
        ]);
    }
}