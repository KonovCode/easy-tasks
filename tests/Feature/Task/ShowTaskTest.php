<?php

namespace Tests\Feature\Task;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_single_task(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'deadline', 'priority', 'status', 'category'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'Test Task',
                ],
            ]);
    }

    public function test_cannot_get_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/tasks/{$task->id}");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Endpoint not found']);
    }

    public function test_unauthenticated_user_cannot_get_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    public function test_task_includes_category_when_assigned(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.category.title', $category->title);
    }

    public function test_task_category_is_null_when_not_assigned(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => null,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.category', null);
    }
}
