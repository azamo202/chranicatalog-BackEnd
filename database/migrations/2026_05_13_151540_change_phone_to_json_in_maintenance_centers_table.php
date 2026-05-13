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
        // تحويل البيانات الموجودة إلى مصفوفة JSON
        $centers = Illuminate\Support\Facades\DB::table('maintenance_centers')->get();
        foreach ($centers as $center) {
            if ($center->phone && !str_starts_with($center->phone, '[')) {
                Illuminate\Support\Facades\DB::table('maintenance_centers')
                    ->where('id', $center->id)
                    ->update(['phone' => json_encode([$center->phone])]);
            }
        }

        Schema::table('maintenance_centers', function (Blueprint $table) {
            $table->json('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_centers', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });
    }
};
