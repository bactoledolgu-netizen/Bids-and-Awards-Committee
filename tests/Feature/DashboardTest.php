<?php

namespace Tests\Feature;

use App\Models\AttendanceFile;
use App\Models\AttendanceFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_real_folder_and_file_statistics(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $folderOne = AttendanceFolder::create([
            'name' => 'Folder One',
            'description' => 'First folder',
            'created_by' => $user->id,
            'folder_date' => '2026-01-01',
            'folder_date_end' => '2026-01-31',
        ]);

        $folderTwo = AttendanceFolder::create([
            'name' => 'Folder Two',
            'description' => 'Second folder',
            'created_by' => $user->id,
            'folder_date' => '2026-02-01',
            'folder_date_end' => '2026-02-28',
        ]);

        $folderTwo->delete();

        AttendanceFile::forceCreate([
            'attendance_folder_id' => $folderOne->id,
            'original_filename' => 'current.pdf',
            'stored_path' => 'attendance/' . $folderOne->id . '/current.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'uploaded_by' => $user->id,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AttendanceFile::forceCreate([
            'attendance_folder_id' => $folderOne->id,
            'original_filename' => 'last-month.pdf',
            'stored_path' => 'attendance/' . $folderOne->id . '/last-month.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4096,
            'uploaded_by' => $user->id,
            'sort_order' => 2,
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        AttendanceFile::forceCreate([
            'attendance_folder_id' => $folderOne->id,
            'original_filename' => 'last-year.pdf',
            'stored_path' => 'attendance/' . $folderOne->id . '/last-year.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 8192,
            'uploaded_by' => $user->id,
            'sort_order' => 3,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['folders'] === 2
                && $stats['files'] === 3
                && $stats['filesThisYear'] === 2
                && $stats['filesThisMonth'] === 1
                && $stats['archivedFolders'] === 1;
        });
    }
}
