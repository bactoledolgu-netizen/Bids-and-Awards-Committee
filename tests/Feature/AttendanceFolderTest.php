<?php

namespace Tests\Feature;

use App\Models\AttendanceFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_folder_with_month_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('attendance.store'), [
            'name' => 'Quarterly Attendance',
            'description' => 'Test folder',
            'start_month' => 1,
            'start_year' => 2026,
            'end_month' => 3,
            'end_year' => 2026,
        ]);

        $response->assertRedirect();

        $folder = AttendanceFolder::latest()->first();

        $this->assertNotNull($folder);
        $this->assertSame('2026-01-01', $folder->folder_date->toDateString());
        $this->assertSame('2026-03-31', $folder->folder_date_end->toDateString());
    }

    public function test_index_groups_folders_by_year_and_orders_newest_first(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AttendanceFolder::create([
            'name' => 'Older Folder',
            'description' => 'Older',
            'created_by' => $user->id,
            'folder_date' => '2024-02-01',
            'folder_date_end' => '2024-02-29',
        ]);

        AttendanceFolder::create([
            'name' => 'Newer Folder',
            'description' => 'Newer',
            'created_by' => $user->id,
            'folder_date' => '2026-03-01',
            'folder_date_end' => '2026-03-31',
        ]);

        AttendanceFolder::create([
            'name' => 'Newest Folder',
            'description' => 'Newest',
            'created_by' => $user->id,
            'folder_date' => '2026-01-01',
            'folder_date_end' => '2026-01-31',
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertOk();
        $response->assertSee('Year 2026');
        $response->assertSee('Year 2024');
        $response->assertSeeInOrder(['Year 2026', 'Newest Folder', 'Newer Folder', 'Year 2024', 'Older Folder']);
    }
}
