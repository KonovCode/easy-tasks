<?php

namespace Tests\Feature\Task;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'description' => 'Task description',
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'deadline', 'priority', 'status', 'category'],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Test Task',
                    'description' => 'Task description',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Test Task',
        ]);
    }

    public function test_can_create_task_with_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.category.title', $category->title);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_can_create_task_with_deadline(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'low',
                'status' => 'pending',
                'deadline' => '2026-12-31 23:59:59',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Test Task',
        ]);
    }

    public function test_cannot_create_task_without_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_create_task_with_empty_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => '',
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_create_task_with_title_exceeding_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => str_repeat('a', 256),
                'priority' => 'low',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_create_task_with_invalid_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'invalid',
                'status' => 'failed',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['priority']]);
    }

    public function test_cannot_create_task_with_invalid_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'low',
                'status' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_cannot_create_task_with_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['category_id']]);
    }

    public function test_cannot_create_task_with_nonexistent_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/tasks', [
                'title' => 'Test Task',
                'priority' => 'low',
                'status' => 'pending',
                'category_id' => 999,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['category_id']]);
    }

    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('api/tasks', [
            'title' => 'Test Task',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response->assertStatus(401);
    }
}
