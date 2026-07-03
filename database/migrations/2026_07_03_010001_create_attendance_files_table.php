<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_folder_id')->constrained('attendance_folders')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_files');
    }
};
