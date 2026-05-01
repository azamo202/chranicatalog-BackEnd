<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HomepageSection;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::with('products')->orderBy('sort_order', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $sections]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $section = HomepageSection::create($validated);

        return response()->json(['status' => 'success', 'data' => $section], 201);
    }

    public function show(string $id)
    {
        $section = HomepageSection::with('products')->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $section]);
    }

    public function update(Request $request, string $id)
    {
        $section = HomepageSection::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|array',
            'title.en' => 'sometimes|string',
            'title.ar' => 'sometimes|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $section->update($validated);

        return response()->json(['status' => 'success', 'data' => $section]);
    }

    public function destroy(string $id)
    {
        $section = HomepageSection::findOrFail($id);
        $section->delete();
        return response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
    }

    public function attachProducts(Request $request, $id)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $section = HomepageSection::findOrFail($id);
        // Sync without detaching to add new ones, or sync() to replace. Let's use syncWithoutDetaching
        $section->products()->syncWithoutDetaching($request->product_ids);

        return response()->json(['status' => 'success', 'message' => 'Products attached successfully']);
    }

    public function detachProducts(Request $request, $id)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $section = HomepageSection::findOrFail($id);
        $section->products()->detach($request->product_ids);

        return response()->json(['status' => 'success', 'message' => 'Products detached successfully']);
    }
}
