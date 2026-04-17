<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDownload;
use App\Http\Resources\SupportDownloadResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportDownloadController extends Controller
{
    public function index()
    {
        return SupportDownloadResource::collection(SupportDownload::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ar' => 'required|string',
            'title.en' => 'nullable|string',
            'title.ku' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:10240',
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
            'file' => 'nullable|mimes:pdf|max:10240',
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
