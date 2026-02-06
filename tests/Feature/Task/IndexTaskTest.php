<?php

namespace Tests\Feature\Task;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_tasks_list(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'deadline', 'priority', 'status', 'category'],
                ],
            ]);
    }

    public function test_returns_empty_array_when_no_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson(['data' => []]);
    }

    public function test_returns_only_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        Task::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_get_tasks(): void
    {
        $response = $this->getJson('api/tasks');

        $response->assertStatus(401);
    }

    public function test_tasks_include_category_when_loaded(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.category.title', $category->title);
    }

    public function test_tasks_are_paginated(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(25)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(20, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_tasks_are_ordered_by_latest(): void
    {
        $user = User::factory()->create();

        $oldTask = Task::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay(),
        ]);

        $newTask = Task::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $newTask->id)
            ->assertJsonPath('data.1.id', $oldTask->id);
    }
}
