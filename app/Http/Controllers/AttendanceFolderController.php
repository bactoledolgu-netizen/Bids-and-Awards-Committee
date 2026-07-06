<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceFolderRequest;
use App\Http\Requests\UpdateAttendanceFolderRequest;
use App\Models\AttendanceFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AttendanceFolderController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceFolder::whereNull('parent_id');

        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $folders = $query->get()->sortByDesc(function ($folder) {
            return optional($folder->folder_date)->format('Y-m-d') ?? $folder->created_at->format('Y-m-d');
        })->values();

        $groupedFolders = $folders
            ->groupBy(function ($folder) {
                return optional($folder->folder_date)->year ?? $folder->created_at->year;
            })
            ->map(function ($yearFolders) {
                return $yearFolders->sortByDesc(function ($folder) {
                    return optional($folder->folder_date)->format('Y-m-d') ?? $folder->created_at->format('Y-m-d');
                })->values();
            })
            ->sortKeysDesc();

        return view('attendance.index', compact('groupedFolders'));
    }

    public function create()
    {
        return view('attendance.create');
    }

    public function store(StoreAttendanceFolderRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if (! empty($data['start_month']) && ! empty($data['start_year'])) {
            $data['folder_date'] = now()->setDate($data['start_year'], $data['start_month'], 1)->toDateString();
        } else {
            $data['folder_date'] = $data['folder_date'] ?? now()->toDateString();
        }

        if (Schema::hasColumn('attendance_folders', 'folder_date_end')) {
            if (! empty($data['end_month']) && ! empty($data['end_year'])) {
                $data['folder_date_end'] = now()->setDate($data['end_year'], $data['end_month'], 1)->endOfMonth()->toDateString();
            } elseif (! empty($data['folder_date'])) {
                $data['folder_date_end'] = null;
            }
        }

        unset($data['start_month'], $data['start_year'], $data['end_month'], $data['end_year']);

        $folder = AttendanceFolder::create($data);

        return redirect()->route('attendance.index')->with('success', 'Folder created.');
    }

    public function show(AttendanceFolder $folder)
    {
        $folder->load(['files', 'children']);
        $ancestors = $folder->ancestors();

        return view('attendance.show', compact('folder', 'ancestors'));
    }

    public function edit(AttendanceFolder $folder)
    {
        return redirect()->route('attendance.show', $folder);
    }

    public function update(UpdateAttendanceFolderRequest $request, AttendanceFolder $folder)
    {
        $data = $request->validated();

        if (! empty($data['start_month']) && ! empty($data['start_year'])) {
            $data['folder_date'] = now()->setDate($data['start_year'], $data['start_month'], 1)->toDateString();
        }

        if (Schema::hasColumn('attendance_folders', 'folder_date_end')) {
            if (! empty($data['end_month']) && ! empty($data['end_year'])) {
                $data['folder_date_end'] = now()->setDate($data['end_year'], $data['end_month'], 1)->endOfMonth()->toDateString();
            } elseif (array_key_exists('end_month', $data) && array_key_exists('end_year', $data)) {
                $data['folder_date_end'] = null;
            }
        }

        unset($data['start_month'], $data['start_year'], $data['end_month'], $data['end_year']);

        $folder->update($data);

        return redirect()->route('attendance.index')->with('success','Folder updated.');
    }

    public function destroy(AttendanceFolder $folder)
    {
        $parent = $folder->parent;
        $folder->delete();

        if ($parent) {
            return redirect()->route('attendance.show', $parent)->with('success', 'Folder deleted (soft).');
        }

        return redirect()->route('attendance.index')->with('success', 'Folder deleted (soft).');
    }
}
