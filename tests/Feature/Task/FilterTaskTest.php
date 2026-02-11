<?php

namespace Tests\Feature\Task;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterTaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'done']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'done']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?status=done');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_tasks_by_priority(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_tasks_by_category(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        Task::factory()->create(['user_id' => $this->user->id, 'category_id' => $category->id]);
        Task::factory()->create(['user_id' => $this->user->id, 'category_id' => $category->id]);
        Task::factory()->create(['user_id' => $this->user->id, 'category_id' => null]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("api/tasks?category={$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_tasks_by_title(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Buy groceries']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Buy new laptop']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Clean the house']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?search=Buy');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_combine_multiple_filters(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'priority' => 'low']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'done', 'priority' => 'high']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?status=pending&priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_no_filters_returns_all_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_invalid_status_returns_validation_error(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?status=invalid');

        $response->assertStatus(422);
    }

    public function test_invalid_priority_returns_validation_error(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('api/tasks?priority=invalid');

        $response->assertStatus(422);
    }
}
