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
        // تحويل البيانات الموجودة إلى مصفوفة JSON لضمان عدم حدوث خطأ عند تغيير النوع
        $settings = Illuminate\Support\Facades\DB::table('store_settings')->get();
        foreach ($settings as $setting) {
            if ($setting->phone && !str_starts_with($setting->phone, '[')) {
                Illuminate\Support\Facades\DB::table('store_settings')
                    ->where('id', $setting->id)
                    ->update(['phone' => json_encode([$setting->phone])]);
            }
        }

        Schema::table('store_settings', function (Blueprint $table) {
            $table->json('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });
    }
};
