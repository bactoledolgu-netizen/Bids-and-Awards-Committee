<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceFolderRequest;
use App\Http\Requests\UpdateAttendanceFolderRequest;
use App\Models\AttendanceFolder;
use Illuminate\Http\Request;

class AttendanceFolderController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceFolder::whereNull('parent_id')->latest();

        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $folders = $query->paginate(12);

        return view('attendance.index', compact('folders'));
    }

    public function create()
    {
        return view('attendance.create');
    }

    public function store(StoreAttendanceFolderRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['folder_date'] = $data['folder_date'] ?? now()->toDateString();
        $folder = AttendanceFolder::create($data);

        return redirect()->route('attendance.show', $folder)->with('success', 'Folder created.');
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
        $folder->update($request->validated());
        return redirect()->route('attendance.show', $folder)->with('success','Folder updated.');
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
