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

        // 3. حذف الـ index من group_name قبل تحويله إلى JSON
        Schema::table('product_specifications', function (Blueprint $table) {
            $table->dropIndex('product_specifications_group_name_index');
        });

        // 4. تحويل المواصفات الفنية
        Schema::table('product_specifications', function (Blueprint $table) {
            $table->json('group_name')->change();
            $table->json('spec_key')->change();
            $table->json('spec_value')->change();
        });

        // 5. تحويل المميزات النقطية
        Schema::table('product_features', function (Blueprint $table) {
            $table->json('feature_text')->change();
        });
    }

    public function down(): void
    {
        // إعادة index إذا احتجت ترجع للخلف
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('product_specifications', function (Blueprint $table) {
            $table->string('group_name')->change();
            $table->string('spec_key')->change();
            $table->text('spec_value')->change();
        });

        Schema::table('product_specifications', function (Blueprint $table) {
            $table->index('group_name');
        });

        Schema::table('product_features', function (Blueprint $table) {
            $table->text('feature_text')->change();
        });
    }
};