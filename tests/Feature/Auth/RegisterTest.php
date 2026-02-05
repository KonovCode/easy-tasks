<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success(): void
    {
        $data = [
            'name' => 'Jhon Psina',
            'email' => 'jhon88@gmail.com',
            'password' => 'pass7777',
            'password_confirmation' => 'pass7777',
        ];

        $response = $this->postJson('api/auth/register', $data);

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user'])
            ->assertJson(['token_type' => 'bearer']);

        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_register_fails_without_password_confirmation(): void
    {
        $data = [
            'name' => 'Jhon Psina',
            'email' => 'jhon@gmail.com',
            'password' => 'test7777',
        ];

        $response = $this->postJson('api/auth/register', $data);

        $this->assertDatabaseMissing('users', ['email' => $data['email']]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_invalid_email(): void
    {
        $data = [
            'name' => 'Jhon Psina',
            'email' => 'jhon.gmail',
            'password' => 'test7777',
            'password_confirmation' => 'test7777',
        ];

        $response = $this->postJson('api/auth/register', $data);

        $this->assertDatabaseMissing('users', ['email' => $data['email']]);

        $response->assertStatus(422);
    }

    public function test_register_fails_user_exist(): void
    {
        $user = User::factory()->create([
            'name' => 'Jhon Psina',
            'email' => 'jhon@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('api/auth/register', $data);

        $response->assertStatus(422);
    }
}
