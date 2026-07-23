<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticeFolderRequest;
use App\Http\Requests\UpdateNoticeFolderRequest;
use App\Models\NoticeFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NoticeFolderController extends Controller
{
    public function index(Request $request)
    {
        $query = NoticeFolder::query();

        if (Schema::hasColumn('notice_folders', 'parent_id')) {
            $query->whereNull('parent_id');
        }

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

        return view('notice.index', compact('groupedFolders'));
    }

    public function create()
    {
        return view('notice.create');
    }

    public function store(StoreNoticeFolderRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if (! empty($data['start_month']) && ! empty($data['start_year'])) {
            $data['folder_date'] = now()->setDate($data['start_year'], $data['start_month'], 1)->toDateString();
        } else {
            $data['folder_date'] = $data['folder_date'] ?? now()->toDateString();
        }

        if (Schema::hasColumn('notice_folders', 'folder_date_end')) {
            if (! empty($data['end_month']) && ! empty($data['end_year'])) {
                $data['folder_date_end'] = now()->setDate($data['end_year'], $data['end_month'], 1)->endOfMonth()->toDateString();
            } elseif (! empty($data['folder_date'])) {
                $data['folder_date_end'] = null;
            }
        }

        unset($data['start_month'], $data['start_year'], $data['end_month'], $data['end_year']);

        $folder = NoticeFolder::create($data);

        return redirect()->route('notice.index')->with('success', 'Folder created.');
    }

    public function show(Request $request, NoticeFolder $folder)
    {
        $folder->load(['children']);
        $fileSearch = trim((string) $request->input('file_search', ''));
        $files = $folder->files()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $ancestors = $folder->ancestors();

        return view('notice.show', compact('folder', 'ancestors', 'files', 'fileSearch'));
    }

    public function edit(NoticeFolder $folder)
    {
        return redirect()->route('notice.show', $folder);
    }

    public function update(UpdateNoticeFolderRequest $request, NoticeFolder $folder)
    {
        $data = $request->validated();

        if (! empty($data['start_month']) && ! empty($data['start_year'])) {
            $data['folder_date'] = now()->setDate($data['start_year'], $data['start_month'], 1)->toDateString();
        }

        if (Schema::hasColumn('notice_folders', 'folder_date_end')) {
            if (! empty($data['end_month']) && ! empty($data['end_year'])) {
                $data['folder_date_end'] = now()->setDate($data['end_year'], $data['end_month'], 1)->endOfMonth()->toDateString();
            } elseif (array_key_exists('end_month', $data) && array_key_exists('end_year', $data)) {
                $data['folder_date_end'] = null;
            }
        }

        unset($data['start_month'], $data['start_year'], $data['end_month'], $data['end_year']);

        $folder->update($data);

        return redirect()->route('notice.index')->with('success','Folder updated.');
    }

    public function destroy(NoticeFolder $folder)
    {
        $parent = $folder->parent;
        $folder->delete();

        if ($parent) {
            return redirect()->route('notice.show', $parent)->with('success', 'Folder deleted (soft).');
        }

        return redirect()->route('notice.index')->with('success', 'Folder deleted (soft).');
    }
}
