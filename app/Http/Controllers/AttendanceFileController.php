<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceFileRequest;
use App\Models\AttendanceFolder;
use App\Models\AttendanceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceFileController extends Controller
{
    public function store(StoreAttendanceFileRequest $request, AttendanceFolder $folder)
    {
        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $uuid = (string) Str::uuid();
            $ext = $file->getClientOriginalExtension();
            $stored = "attendance/{$folder->id}/{$uuid}.{$ext}";
            Storage::disk('local')->putFileAs("attendance/{$folder->id}", $file, "{$uuid}.{$ext}");

            $nextOrder = AttendanceFile::where('attendance_folder_id', $folder->id)->max('sort_order') ?? 0;

            $af = AttendanceFile::create([
                'attendance_folder_id' => $folder->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $stored,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'sort_order' => $nextOrder + 1,
            ]);

            $uploaded[] = $af;
        }

        return redirect()->route('attendance.show', $folder)->with('success','Files uploaded.');
    }

    public function show(AttendanceFolder $folder, AttendanceFile $file)
    {
        // ensure file belongs to folder
        if ($file->attendance_folder_id !== $folder->id) {
            abort(404);
        }

        // stream from storage local disk (private)
        $path = Storage::disk('local')->path($file->stored_path);
        return response()->file($path);
    }

    public function destroy(AttendanceFolder $folder, AttendanceFile $file)
    {
        if ($file->attendance_folder_id !== $folder->id) {
            abort(404);
        }

        // delete stored file
        Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return redirect()->route('attendance.show', $folder)->with('success','File deleted.');
    }

    public function reorder(Request $request, AttendanceFolder $folder)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $fileId) {
            AttendanceFile::where('attendance_folder_id', $folder->id)
                ->where('id', $fileId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
