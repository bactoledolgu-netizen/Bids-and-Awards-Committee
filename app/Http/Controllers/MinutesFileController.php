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
        $renamed = [];
        foreach ($request->file('files') as $index => $file) {
            if (! $file->isValid() || blank($file->getRealPath())) {
                return back()->withErrors([
                    "files.{$index}" => $file->getErrorMessage() ?: 'The uploaded file could not be read from PHP temporary storage.',
                ])->withInput();
            }

            $uuid = (string) Str::uuid();
            $ext = $file->getClientOriginalExtension();
            $stored = "minutes/{$folder->id}/{$uuid}.{$ext}";
            $disk = Storage::disk('local');
            if (! $disk->putFileAs("minutes/{$folder->id}", $file, "{$uuid}.{$ext}")) {
                return back()->withErrors([
                    "files.{$index}" => 'The file could not be saved to application storage.',
                ])->withInput();
            }

            $nextOrder = MinutesFile::where('minutes_folder_id', $folder->id)->max('sort_order') ?? 0;
            $originalFilename = $file->getClientOriginalName();
            $displayFilename = $this->uniqueOriginalFilename($originalFilename, $folder->id);

            $af = MinutesFile::create([
                'minutes_folder_id' => $folder->id,
                'original_filename' => $displayFilename,
                'stored_path' => $stored,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'sort_order' => $nextOrder + 1,
            ]);

            $uploaded[] = $af;
            if ($displayFilename !== $originalFilename) {
                $renamed[] = "{$originalFilename} -> {$displayFilename}";
            }
        }

        $message = 'Files uploaded.';
        if ($renamed) {
            $message .= ' Renamed: '.implode(', ', $renamed);
        }

        return redirect()->route('minutes.show', $folder)->with('success', $message);
    }

    private function uniqueOriginalFilename(string $originalFilename, int $folderId): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $candidate = $originalFilename;
        $copyNumber = 2;

        while (MinutesFile::where('minutes_folder_id', $folderId)
            ->where('original_filename', $candidate)
            ->exists()) {
            $candidate = $basename.' ('.$copyNumber.')'.($extension ? '.'.$extension : '');
            $copyNumber++;
        }

        return $candidate;
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

        $disk = Storage::disk('local');
        if (blank($file->stored_path) || ! $disk->exists($file->stored_path)) {
            return redirect()->route('minutes.show', $folder)
                ->with('warning', 'This file is unavailable because its stored file is missing.');
        }

        // stream from storage local disk (private)
        $path = $disk->path($file->stored_path);
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
