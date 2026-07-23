<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeFile extends Model
{
    use HasFactory;

    protected $fillable = ['notice_folder_id','original_filename','stored_path','mime_type','file_size','uploaded_by','sort_order'];

    public function folder()
    {
        return $this->belongsTo(NoticeFolder::class, 'notice_folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
