<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceFile extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_folder_id','original_filename','stored_path','mime_type','file_size','uploaded_by','sort_order'];

    public function folder()
    {
        return $this->belongsTo(AttendanceFolder::class, 'attendance_folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
