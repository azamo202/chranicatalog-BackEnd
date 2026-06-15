<?php
/**
 * سكريبت إصلاح بيانات sort_order التالفة (تكرار أو فراغات)
 * يُنفَّذ مرة واحدة لإصلاح البيانات الموجودة في قاعدة البيانات
 * 
 * الاستخدام: php artisan tinker < fix_sort_order.php
 * أو: php fix_sort_order.php (بعد تعديل مسار autoload)
 */

// ─── إصلاح المنتجات (مُجمَّعة حسب category_id) ────────────────────────────
echo "📦 إصلاح ترتيب المنتجات...\n";
$categories = DB::table('products')->distinct()->pluck('category_id');
foreach ($categories as $catId) {
    $products = DB::table('products')
        ->where('category_id', $catId)
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'asc')
        ->get(['id', 'sort_order']);

    $counter = 1;
    foreach ($products as $p) {
        if ((int)$p->sort_order !== $counter) {
            DB::table('products')->where('id', $p->id)->update(['sort_order' => $counter]);
            echo "  ✔ منتج #$p->id: {$p->sort_order} → {$counter} (قسم: {$catId})\n";
        }
        $counter++;
    }
}
echo "✅ تم إصلاح ترتيب المنتجات.\n\n";

// ─── إصلاح الأقسام الرئيسية ────────────────────────────────────────────────
echo "🗂️  إصلاح ترتيب الأقسام الرئيسية...\n";
$categories = DB::table('categories')
    ->whereNull('parent_id')
    ->orderBy('sort_order', 'asc')
    ->orderBy('id', 'asc')
    ->get(['id', 'sort_order']);

$counter = 1;
foreach ($categories as $cat) {
    if ((int)$cat->sort_order !== $counter) {
        DB::table('categories')->where('id', $cat->id)->update(['sort_order' => $counter]);
        echo "  ✔ قسم #$cat->id: {$cat->sort_order} → {$counter}\n";
    }
    $counter++;
}
echo "✅ تم إصلاح ترتيب الأقسام.\n\n";

// ─── إصلاح مراكز الصيانة ───────────────────────────────────────────────────
echo "🔧 إصلاح ترتيب مراكز الصيانة...\n";
$centers = DB::table('maintenance_centers')
    ->orderBy('sort_order', 'asc')
    ->orderBy('id', 'asc')
    ->get(['id', 'sort_order']);

$counter = 1;
foreach ($centers as $c) {
    if ((int)$c->sort_order !== $counter) {
        DB::table('maintenance_centers')->where('id', $c->id)->update(['sort_order' => $counter]);
        echo "  ✔ مركز #$c->id: {$c->sort_order} → {$counter}\n";
    }
    $counter++;
}
echo "✅ تم إصلاح ترتيب مراكز الصيانة.\n\n";

// ─── إصلاح فيديوهات الدعم ─────────────────────────────────────────────────
echo "🎬 إصلاح ترتيب فيديوهات الدعم...\n";
$videos = DB::table('support_videos')
    ->orderBy('sort_order', 'asc')
    ->orderBy('id', 'asc')
    ->get(['id', 'sort_order']);

$counter = 1;
foreach ($videos as $v) {
    if ((int)$v->sort_order !== $counter) {
        DB::table('support_videos')->where('id', $v->id)->update(['sort_order' => $counter]);
        echo "  ✔ فيديو #$v->id: {$v->sort_order} → {$counter}\n";
    }
    $counter++;
}
echo "✅ تم إصلاح ترتيب الفيديوهات.\n\n";

// ─── إصلاح ملفات الدعم ────────────────────────────────────────────────────
echo "📄 إصلاح ترتيب ملفات الدعم...\n";
$downloads = DB::table('support_downloads')
    ->orderBy('sort_order', 'asc')
    ->orderBy('id', 'asc')
    ->get(['id', 'sort_order']);

$counter = 1;
foreach ($downloads as $d) {
    if ((int)$d->sort_order !== $counter) {
        DB::table('support_downloads')->where('id', $d->id)->update(['sort_order' => $counter]);
        echo "  ✔ ملف #$d->id: {$d->sort_order} → {$counter}\n";
    }
    $counter++;
}
echo "✅ تم إصلاح ترتيب الملفات.\n\n";

echo "🎉 تم إصلاح جميع بيانات الترتيب بنجاح!\n";
