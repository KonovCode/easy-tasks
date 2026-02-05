<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_single_category(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
            'title' => 'Work',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'created_at']
            ])
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                    'title' => 'Work',
                ]
            ]);
    }

    public function test_cannot_get_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("api/categories/{$category->id}");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('api/categories/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Endpoint not found']);
    }

    public function test_unauthenticated_user_cannot_get_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("api/categories/{$category->id}");

        $response->assertStatus(401);
    }
}
