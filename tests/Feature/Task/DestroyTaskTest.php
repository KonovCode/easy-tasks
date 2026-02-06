<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_task(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("api/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_cannot_delete_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("api/tasks/{$task->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->deleteJson('api/tasks/999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("api/tasks/{$task->id}");

        $response->assertStatus(401);
    }
}
