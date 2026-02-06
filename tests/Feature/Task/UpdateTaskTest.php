<?php

namespace Tests\Feature\Task;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_task(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => 'New Title',
                'priority' => 'high',
                'status' => 'done',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'deadline', 'priority', 'status', 'category'],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'New Title',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Title',
            'priority' => 'high',
            'status' => 'done',
        ]);
    }

    public function test_can_update_task_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => null,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => $task->title,
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.category.title', $category->title);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_can_remove_task_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => $task->title,
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => null,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.category', null);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'category_id' => null,
        ]);
    }

    public function test_cannot_update_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => 'Hacked',
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->putJson('api/tasks/999', [
                'title' => 'New Title',
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_update_task_without_title(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_update_task_with_empty_title(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => '',
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_update_task_with_invalid_priority(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => 'Test',
                'priority' => 'invalid',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['priority']]);
    }

    public function test_cannot_update_task_with_invalid_status(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => 'Test',
                'priority' => 'low',
                'status' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_cannot_update_task_with_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/tasks/{$task->id}", [
                'title' => 'Test',
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['category_id']]);
    }

    public function test_unauthenticated_user_cannot_update_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->putJson("api/tasks/{$task->id}", [
            'title' => 'New Title',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response->assertStatus(401);
    }
}
