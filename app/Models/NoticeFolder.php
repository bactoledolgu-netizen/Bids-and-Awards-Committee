<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoticeFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','folder_date','folder_date_end','description','venue','created_by','parent_id'];

    protected $casts = [
        'folder_date' => 'date',
        'folder_date_end' => 'date',
    ];

    public function files()
    {
        return $this->hasMany(NoticeFile::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent;
            $parent = $parent->parent;
        }

        return array_reverse($ancestors);
    }
}
