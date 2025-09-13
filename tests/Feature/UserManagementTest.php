<?php
// tests/Feature/UserManagementTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\User;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_get_all_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_get_specific_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function test_caching_implementation(): void
    {
        $user = User::factory()->create();

        // First call - should hit database and cache the result
        $response1 = $this->getJson('/api/users');
        $response1->assertStatus(200);

        // Second call - should use cache
        $response2 = $this->getJson('/api/users');
        $response2->assertStatus(200);

        // Verify both responses are identical
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function test_cache_invalidation_on_update(): void
    {
        $user = User::factory()->create();

        // Get users to populate cache
        $this->getJson('/api/users');

        // Update user - should invalidate cache
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $this->putJson("/api/users/{$user->id}", $updateData);

        // Get users again - should hit database (cache was invalidated)
        $response = $this->getJson('/api/users');
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]);
    }
}