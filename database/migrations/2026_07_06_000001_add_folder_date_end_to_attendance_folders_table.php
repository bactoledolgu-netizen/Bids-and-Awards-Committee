<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendance_folders', 'folder_date_end')) {
            Schema::table('attendance_folders', function (Blueprint $table) {
                $table->date('folder_date_end')->nullable()->after('folder_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_folders', 'folder_date_end')) {
            Schema::table('attendance_folders', function (Blueprint $table) {
                $table->dropColumn('folder_date_end');
            });
        }
    }
};
