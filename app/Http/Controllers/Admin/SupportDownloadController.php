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
        $query = SupportDownload::query();

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
        ]);

        $path = $request->file('file')->store('support_docs', 'public');
        $download = SupportDownload::create([
            'title' => $request->title,
            'pdf_file_path' => $path
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
        ]);

        $data = ['title' => $request->title];

        if ($request->hasFile('file')) {
            // حذف الملف القديم من التخزين
            Storage::disk('public')->delete($download->pdf_file_path);
            // رفع الملف الجديد
            $data['pdf_file_path'] = $request->file('file')->store('support_docs', 'public');
        }

        $download->update($data);

        return response()->json(['status' => true, 'message' => 'تم تحديث الملف بنجاح', 'data' => new SupportDownloadResource($download)]);
    }

    public function destroy($id)
    {
        $download = SupportDownload::findOrFail($id);
        Storage::disk('public')->delete($download->pdf_file_path);
        $download->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف الملف بنجاح']);
    }
}