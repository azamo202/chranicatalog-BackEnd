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
        Schema::create('support_downloads', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // عنوان الملف مثل "دليل تشغيل الفرن" (متعدد اللغات)
            $table->string('pdf_file_path'); // مسار ملف الـ PDF في السيرفر
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_downloads');
    }
};
