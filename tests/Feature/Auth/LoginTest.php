<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success(): void
    {
        User::factory()->create([
            'email' => 'jhon@gmail.com',
            'password' => Hash::make('pass7777'),
        ]);

        $data = [
            'email' => 'jhon@gmail.com',
            'password' => 'pass7777',
        ];

        $response = $this->postJson('api/auth/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user'])
            ->assertJson(['token_type' => 'bearer']);

        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'jhon@gmail.com',
            'password' => Hash::make('pass7777'),
        ]);

        $data = [
            'email' => 'jhon@gmail.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('api/auth/login', $data);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $data = [
            'email' => 'notexist@gmail.com',
            'password' => 'pass7777',
        ];

        $response = $this->postJson('api/auth/login', $data);

        $response->assertStatus(401);
    }
}
