<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticeFileRequest;
use App\Models\NoticeFolder;
use App\Models\NoticeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Setting;

class NoticeFileController extends Controller
{
    public function store(StoreNoticeFileRequest $request, NoticeFolder $folder)
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
            $stored = "notice/{$folder->id}/{$uuid}.{$ext}";
            $disk = Storage::disk('local');
            if (! $disk->putFileAs("notice/{$folder->id}", $file, "{$uuid}.{$ext}")) {
                return back()->withErrors([
                    "files.{$index}" => 'The file could not be saved to application storage.',
                ])->withInput();
            }

            $nextOrder = NoticeFile::where('notice_folder_id', $folder->id)->max('sort_order') ?? 0;
            $originalFilename = $file->getClientOriginalName();
            $displayFilename = $this->uniqueOriginalFilename($originalFilename, $folder->id);

            $af = NoticeFile::create([
                'notice_folder_id' => $folder->id,
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

        return redirect()->route('notice.show', $folder)->with('success', $message);
    }

    private function uniqueOriginalFilename(string $originalFilename, int $folderId): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $candidate = $originalFilename;
        $copyNumber = 2;

        while (NoticeFile::where('notice_folder_id', $folderId)
            ->where('original_filename', $candidate)
            ->exists()) {
            $candidate = $basename.' ('.$copyNumber.')'.($extension ? '.'.$extension : '');
            $copyNumber++;
        }

        return $candidate;
    }

    public function show(NoticeFolder $folder, NoticeFile $file)
    {
        // ensure file belongs to folder
        if ($file->notice_folder_id !== $folder->id) {
            abort(404);
        }

        if (! session()->get("notice_file_view_verified.{$file->id}", false)) {
            return redirect()->route('notice.show', $folder)
                ->with('warning', 'Please enter the notice password to view this file.')
                ->withInput(['file_id' => $file->id]);
        }

        $disk = Storage::disk('local');
        if (blank($file->stored_path) || ! $disk->exists($file->stored_path)) {
            return redirect()->route('notice.show', $folder)
                ->with('warning', 'This file is unavailable because its stored file is missing.');
        }

        // stream from storage local disk (private)
        $path = $disk->path($file->stored_path);
        return response()->file($path);
    }

    public function verify(Request $request, NoticeFolder $folder, NoticeFile $file)
    {
        if ($file->notice_folder_id !== $folder->id) {
            abort(404);
        }

        $request->validate([
            'attendance_password' => 'required|string',
        ]);

        $hashedPassword = Setting::get('attendance_password');

        if (! $hashedPassword || ! Hash::check($request->input('attendance_password'), $hashedPassword)) {
            return redirect()->route('notice.show', $folder)
                ->withErrors(['attendance_password' => 'Invalid attendance password.'])
                ->withInput(['file_id' => $file->id]);
        }

        session()->put("notice_file_view_verified.{$file->id}", true);

        return redirect()->route('notice.files.show', [$folder, $file]);
    }

    public function destroy(NoticeFolder $folder, NoticeFile $file)
    {
        if ($file->notice_folder_id !== $folder->id) {
            abort(404);
        }

        // delete stored file
        Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return redirect()->route('notice.show', $folder)->with('success','File deleted.');
    }

    public function bulkDestroy(Request $request, NoticeFolder $folder)
    {
        $fileIds = $request->input('file_ids', []);

        if (!empty($fileIds)) {
            $files = NoticeFile::where('notice_folder_id', $folder->id)
                ->whereIn('id', $fileIds)
                ->get();

            foreach ($files as $file) {
                Storage::disk('local')->delete($file->stored_path);
                $file->delete();
            }
        }

        return redirect()->route('notice.show', $folder)->with('success','Selected files deleted.');
    }

    public function reorder(Request $request, NoticeFolder $folder)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $fileId) {
            NoticeFile::where('notice_folder_id', $folder->id)
                ->where('id', $fileId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
