<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notice_folders', function (Blueprint $table) {
            if (! Schema::hasColumn('notice_folders', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('notice_folders')->nullOnDelete();
            }

            if (! Schema::hasColumn('notice_folders', 'name')) {
                $table->string('name');
            }

            if (! Schema::hasColumn('notice_folders', 'folder_date')) {
                $table->date('folder_date')->nullable();
            }

            if (! Schema::hasColumn('notice_folders', 'folder_date_end')) {
                $table->date('folder_date_end')->nullable();
            }

            if (! Schema::hasColumn('notice_folders', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('notice_folders', 'venue')) {
                $table->string('venue')->nullable();
            }

            if (! Schema::hasColumn('notice_folders', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('notice_folders', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('notice_files', function (Blueprint $table) {
            if (! Schema::hasColumn('notice_files', 'notice_folder_id')) {
                $table->foreignId('notice_folder_id')->constrained('notice_folders')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('notice_files', 'original_filename')) {
                $table->string('original_filename');
            }

            if (! Schema::hasColumn('notice_files', 'stored_path')) {
                $table->string('stored_path');
            }

            if (! Schema::hasColumn('notice_files', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }

            if (! Schema::hasColumn('notice_files', 'file_size')) {
                $table->unsignedInteger('file_size')->nullable();
            }

            if (! Schema::hasColumn('notice_files', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('notice_files', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('notice_files', function (Blueprint $table) {
            if (Schema::hasColumn('notice_files', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('notice_files', 'uploaded_by')) {
                $table->dropForeign(['uploaded_by']);
                $table->dropColumn('uploaded_by');
            }

            if (Schema::hasColumn('notice_files', 'file_size')) {
                $table->dropColumn('file_size');
            }

            if (Schema::hasColumn('notice_files', 'mime_type')) {
                $table->dropColumn('mime_type');
            }

            if (Schema::hasColumn('notice_files', 'stored_path')) {
                $table->dropColumn('stored_path');
            }

            if (Schema::hasColumn('notice_files', 'original_filename')) {
                $table->dropColumn('original_filename');
            }

            if (Schema::hasColumn('notice_files', 'notice_folder_id')) {
                $table->dropForeign(['notice_folder_id']);
                $table->dropColumn('notice_folder_id');
            }
        });

        Schema::table('notice_folders', function (Blueprint $table) {
            if (Schema::hasColumn('notice_folders', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            if (Schema::hasColumn('notice_folders', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('notice_folders', 'venue')) {
                $table->dropColumn('venue');
            }

            if (Schema::hasColumn('notice_folders', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('notice_folders', 'folder_date_end')) {
                $table->dropColumn('folder_date_end');
            }

            if (Schema::hasColumn('notice_folders', 'folder_date')) {
                $table->dropColumn('folder_date');
            }

            if (Schema::hasColumn('notice_folders', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('notice_folders', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
