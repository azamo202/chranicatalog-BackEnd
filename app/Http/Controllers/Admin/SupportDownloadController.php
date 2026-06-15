<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDownload;
use App\Http\Resources\SupportDownloadResource;
use App\Traits\ManagesSortOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportDownloadController extends Controller
{
    use ManagesSortOrder;

    public function index(Request $request)
    {
        $query = SupportDownload::query()->orderBy('sort_order', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title->ar', 'LIKE', "%{$search}%")
                  ->orWhere('title->en', 'LIKE', "%{$search}%")
                  ->orWhere('title->ku', 'LIKE', "%{$search}%");
            });
        }

        return SupportDownloadResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|array',
            'title.ar'   => 'required|string',
            'title.en'   => 'nullable|string',
            'title.ku'   => 'nullable|string',
            'file'       => 'required|file|mimes:pdf,jpeg,png,jpg,webp,doc,docx,xls,xlsx,zip,rar|max:51200',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        // رفع الملف خارج الـ transaction لتقليل وقت القفل
        $path = $request->file('file')->store('support_docs', 'public');

        $download = DB::transaction(function () use ($request, $path) {
            $sortOrder = $this->adjustSortOrder(
                null,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                SupportDownload::query()
            );

            return SupportDownload::create([
                'title'         => $request->title,
                'pdf_file_path' => $path,
                'sort_order'    => $sortOrder,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم رفع الملف بنجاح',
            'data'    => new SupportDownloadResource($download),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'      => 'required|array',
            'title.ar'   => 'required|string',
            'title.en'   => 'nullable|string',
            'title.ku'   => 'nullable|string',
            'file'       => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp,doc,docx,xls,xlsx,zip,rar|max:51200',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        // رفع الملف الجديد خارج الـ transaction (إن وُجد)
        $newFilePath = null;
        if ($request->hasFile('file')) {
            $newFilePath = $request->file('file')->store('support_docs', 'public');
        }

        $download = DB::transaction(function () use ($request, $id, $newFilePath) {
            $download  = SupportDownload::findOrFail($id);
            $oldPath   = $download->pdf_file_path;

            $sortOrder = $this->adjustSortOrder(
                $download->id,
                $request->filled('sort_order') ? (int)$request->sort_order : null,
                SupportDownload::query()
            );

            $data = [
                'title'      => $request->title,
                'sort_order' => $sortOrder,
            ];

            if ($newFilePath) {
                $data['pdf_file_path'] = $newFilePath;
                // حذف الملف القديم بعد التأكد من نجاح الحفظ
                Storage::disk('public')->delete($oldPath);
            }

            $download->update($data);
            return $download->fresh();
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث الملف بنجاح',
            'data'    => new SupportDownloadResource($download),
        ]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $download     = SupportDownload::findOrFail($id);
            $deletedOrder = (int) $download->sort_order;
            $filePath     = $download->pdf_file_path;

            $download->delete();
            Storage::disk('public')->delete($filePath);

            $this->reorderAfterDelete(SupportDownload::query(), $deletedOrder);
        });

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الملف بنجاح',
        ]);
    }

    /**
     * إصلاح الترتيب الكامل للملفات (يُعالج البيانات التالفة)
     */
    public function normalize()
    {
        $this->normalizeOrder(SupportDownload::query());

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة ترتيب الملفات بنجاح',
        ]);
    }
}