<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDownload;
use App\Http\Resources\SupportDownloadResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportDownloadController extends Controller
{
    public function index(Request $request)
    {
        // 1. بدء الاستعلام
        $query = SupportDownload::query()->orderBy('sort_order', 'asc');

        // 2. الفلترة (البحث في عنوان الملف بجميع اللغات)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        // 3. جلب البيانات
        return SupportDownloadResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'title.en' => 'nullable|string',
            'title.ku' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpeg,png,jpg,webp,doc,docx,xls,xlsx,zip,rar|max:10240',
            'sort_order' => 'nullable|integer',
        ]);

        $path = $request->file('file')->store('support_docs', 'public');
        $sortOrder = $this->adjustSortOrder(null, $request->sort_order ?? null, SupportDownload::query());

        $download = SupportDownload::create([
            'title' => $request->title,
            'pdf_file_path' => $path,
            'sort_order' => $sortOrder,
        ]);

        return response()->json(['status' => true, 'message' => 'تم رفع الملف بنجاح', 'data' => new SupportDownloadResource($download)], 201);
    }

    public function update(Request $request, $id)
    {
        $download = SupportDownload::findOrFail($id);

        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'title.en' => 'nullable|string',
            'title.ku' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp,doc,docx,xls,xlsx,zip,rar|max:10240',
            'sort_order' => 'nullable|integer',
        ]);

        $data = ['title' => $request->title];
        $sortOrder = $this->adjustSortOrder($download->id, $request->sort_order ?? null, SupportDownload::query());
        $data['sort_order'] = $sortOrder;

        if ($request->hasFile('file')) {
            // حذف الملف القديم من التخزين
            Storage::disk('public')->delete($download->pdf_file_path);
            // رفع الملف الجديد
            $data['pdf_file_path'] = $request->file('file')->store('support_docs', 'public');
        }

        $download->update($data);

        return response()->json(['status' => true, 'message' => 'تم تحديث الملف بنجاح', 'data' => new SupportDownloadResource($download)]);
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
        $download = SupportDownload::findOrFail($id);
        Storage::disk('public')->delete($download->pdf_file_path);
        $download->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف الملف بنجاح']);
    }
}