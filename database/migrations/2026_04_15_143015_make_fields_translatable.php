<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. تحويل اسم القسم
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // 2. تحويل اسم ووصف المنتج
        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // 3. تحويل المواصفات الفنية
        Schema::table('product_specifications', function (Blueprint $table) {
            $table->json('group_name')->change();
            $table->json('spec_key')->change();
            $table->json('spec_value')->change();
        });

        // 4. تحويل المميزات النقطية
        Schema::table('product_features', function (Blueprint $table) {
            $table->json('feature_text')->change();
        });
    }

    public function down(): void
    {
        // للعودة للحالة السابقة (اختياري)
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
        });
        // ... (باقي الجداول)
    }
};