<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMinutesFileRequest;
use App\Models\MinutesFolder;
use App\Models\MinutesFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Setting;

class MinutesFileController extends Controller
{
    public function store(StoreMinutesFileRequest $request, MinutesFolder $folder)
    {
        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $uuid = (string) Str::uuid();
            $ext = $file->getClientOriginalExtension();
            $stored = "minutes/{$folder->id}/{$uuid}.{$ext}";
            Storage::disk('local')->putFileAs("minutes/{$folder->id}", $file, "{$uuid}.{$ext}");

            $nextOrder = MinutesFile::where('minutes_folder_id', $folder->id)->max('sort_order') ?? 0;

            $af = MinutesFile::create([
                'minutes_folder_id' => $folder->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $stored,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'sort_order' => $nextOrder + 1,
            ]);

            $uploaded[] = $af;
        }

        return redirect()->route('minutes.show', $folder)->with('success','Files uploaded.');
    }

    public function show(MinutesFolder $folder, MinutesFile $file)
    {
        // ensure file belongs to folder
        if ($file->minutes_folder_id !== $folder->id) {
            abort(404);
        }

        if (! session()->get("minutes_file_view_verified.{$file->id}", false)) {
            return redirect()->route('minutes.show', $folder)
                ->with('warning', 'Please enter the minutes password to view this file.')
                ->withInput(['file_id' => $file->id]);
        }

        // stream from storage local disk (private)
        $path = Storage::disk('local')->path($file->stored_path);
        return response()->file($path);
    }

    public function verify(Request $request, MinutesFolder $folder, MinutesFile $file)
    {
        if ($file->minutes_folder_id !== $folder->id) {
            abort(404);
        }

        $request->validate([
        'attendance_password' => 'required|string',
        ]);

        $hashedPassword = Setting::get('attendance_password');

        if (! $hashedPassword || ! Hash::check($request->input('attendance_password'), $hashedPassword)) {
            return redirect()->route('minutes.show', $folder)
                ->withErrors(['attendance_password' => 'Invalid attendance password.'])
                ->withInput(['file_id' => $file->id]);
        }

        session()->put("minutes_file_view_verified.{$file->id}", true);

        return redirect()->route('minutes.files.show', [$folder, $file]);
    }

    public function destroy(MinutesFolder $folder, MinutesFile $file)
    {
        if ($file->minutes_folder_id !== $folder->id) {
            abort(404);
        }

        // delete stored file
        Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return redirect()->route('minutes.show', $folder)->with('success','File deleted.');
    }

    public function bulkDestroy(Request $request, MinutesFolder $folder)
    {
        $fileIds = $request->input('file_ids', []);

        if (!empty($fileIds)) {
            $files = MinutesFile::where('minutes_folder_id', $folder->id)
                ->whereIn('id', $fileIds)
                ->get();

            foreach ($files as $file) {
                Storage::disk('local')->delete($file->stored_path);
                $file->delete();
            }
        }

        return redirect()->route('minutes.show', $folder)->with('success','Selected files deleted.');
    }

    public function reorder(Request $request, MinutesFolder $folder)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $fileId) {
            MinutesFile::where('minutes_folder_id', $folder->id)
                ->where('id', $fileId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
