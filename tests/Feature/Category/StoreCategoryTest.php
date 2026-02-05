<?php

namespace Tests\Feature\Category;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/categories', [
                'title' => 'Work',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'created_at']
            ])
            ->assertJson([
                'data' => ['title' => 'Work']
            ]);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'title' => 'Work',
        ]);
    }

    public function test_cannot_create_category_without_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/categories', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['title']);
    }

    public function test_cannot_create_category_with_empty_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/categories', [
                'title' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['title']);
    }

    public function test_cannot_create_category_with_title_exceeding_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/categories', [
                'title' => str_repeat('a', 256),
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['title']);
    }

    public function test_unauthenticated_user_cannot_create_category(): void
    {
        $response = $this->postJson('api/categories', [
            'title' => 'Work',
        ]);

        $response->assertStatus(401);
    }
}
