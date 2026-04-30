<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_centers', function (Blueprint $table) {
            // إضافة حقل المدينة كـ JSON ليدعم اللغات الثلاث
            $table->json('city')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_centers', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};