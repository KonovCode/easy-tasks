<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_category(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/categories/{$category->id}", [
                'title' => 'New Title',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'created_at']
            ])
            ->assertJson([
                'data' => ['title' => 'New Title']
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'title' => 'New Title',
        ]);
    }

    public function test_cannot_update_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/categories/{$category->id}", [
                'title' => 'Hacked',
            ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->putJson('api/categories/999', [
                'title' => 'New Title',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_update_category_without_title(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/categories/{$category->id}", []);

        $response->assertStatus(422)
            ->assertJsonStructure(['title']);
    }

    public function test_cannot_update_category_with_empty_title(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("api/categories/{$category->id}", [
                'title' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['title']);
    }

    public function test_unauthenticated_user_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("api/categories/{$category->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(401);
    }
}
