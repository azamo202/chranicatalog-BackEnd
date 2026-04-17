<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // نستخدم enum لتحديد قيم ثابتة لا يمكن إدخال غيرها، ونجعل admin هو الافتراضي
            $table->enum('role', ['admin', 'super_admin'])->default('admin')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};