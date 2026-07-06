<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendance_files', 'sort_order')) {
            Schema::table('attendance_files', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('uploaded_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_files', 'sort_order')) {
            Schema::table('attendance_files', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
