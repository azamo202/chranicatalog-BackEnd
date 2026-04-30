<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. تحويل البيانات القديمة (النصوص العادية) إلى صيغة JSON صالحة أولاً
        \Illuminate\Support\Facades\DB::table('products')
            ->whereNotNull('origin_country')
            ->orderBy('id')
            ->chunk(100, function ($products) {
                foreach ($products as $product) {
                    $value = $product->origin_country;
                    // التأكد من أن القيمة ليست JSON بالفعل
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // تحويل النص العادي إلى مصفوفة JSON للغات الثلاث
                        $jsonValue = json_encode([
                            'ar' => $value,
                            'en' => $value,
                            'ku' => $value,
                        ], JSON_UNESCAPED_UNICODE);
                        
                        \Illuminate\Support\Facades\DB::table('products')
                            ->where('id', $product->id)
                            ->update(['origin_country' => $jsonValue]);
                    }
                }
            });

        Schema::table('products', function (Blueprint $table) {
            // Change the column to JSON to support multiple languages
            // Make sure you have `doctrine/dbal` installed: `composer require doctrine/dbal`
            $table->json('origin_country')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to string. Note: This might cause data loss for translations.
            $table->string('origin_country')->nullable()->change();
        });

        // إرجاع البيانات إلى نصوص عادية في حال التراجع عن ملف الترحيل
        \Illuminate\Support\Facades\DB::table('products')
            ->whereNotNull('origin_country')
            ->orderBy('id')
            ->chunk(100, function ($products) {
                foreach ($products as $product) {
                    $value = $product->origin_country;
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // أخذ القيمة العربية أو الإنجليزية كقيمة افتراضية عند التراجع
                        $stringValue = $decoded['ar'] ?? $decoded['en'] ?? '';
                        \Illuminate\Support\Facades\DB::table('products')
                            ->where('id', $product->id)
                            ->update(['origin_country' => $stringValue]);
                    }
                }
            });
    }
};