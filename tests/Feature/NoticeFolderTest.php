<?php

namespace Tests\Feature;

use App\Models\NoticeFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_notice_folders_can_be_queried_by_parent_id(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        NoticeFolder::create([
            'name' => 'Quarterly Notices',
            'description' => 'Test folder',
            'created_by' => $user->id,
            'folder_date' => '2026-07-01',
        ]);

        $folders = NoticeFolder::whereNull('parent_id')->get();

        $this->assertCount(1, $folders);
        $this->assertSame('Quarterly Notices', $folders->first()->name);
    }
}
