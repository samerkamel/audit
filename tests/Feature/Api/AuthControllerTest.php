<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->testUser = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                    'token_type',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotNull($response->json('data.token'));
    }

    /** @test */
    public function it_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_cannot_login_with_invalid_password()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_requires_device_name_for_login()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }

    /** @test */
    public function it_can_logout_with_valid_token()
    {
        $token = $this->testUser->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // Verify token was revoked
        $this->assertEquals(0, $this->testUser->tokens()->count());
    }

    /** @test */
    public function it_can_logout_all_devices()
    {
        // Create multiple tokens
        $this->testUser->createToken('device1');
        $this->testUser->createToken('device2');
        $token = $this->testUser->createToken('device3')->plainTextToken;

        $this->assertEquals(3, $this->testUser->tokens()->count());

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out from all devices successfully',
            ]);

        // Verify all tokens were revoked
        $this->assertEquals(0, $this->testUser->tokens()->count());
    }

    /** @test */
    public function it_can_get_user_profile()
    {
        $token = $this->testUser->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'name' => 'Test User',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_list_active_tokens()
    {
        $this->testUser->createToken('device1');
        $this->testUser->createToken('device2');
        $token = $this->testUser->createToken('device3')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/tokens');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tokens' => [
                        '*' => [
                            'id',
                            'name',
                            'abilities',
                            'last_used_at',
                            'created_at',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data.tokens'));
    }

    /** @test */
    public function it_can_revoke_specific_token()
    {
        $token1 = $this->testUser->createToken('device1');
        $token2 = $this->testUser->createToken('device2')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->deleteJson('/api/v1/auth/tokens/' . $token1->accessToken->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token revoked successfully',
            ]);

        $this->assertEquals(1, $this->testUser->tokens()->count());
    }

    /** @test */
    public function it_requires_authentication_for_protected_endpoints()
    {
        $response = $this->getJson('/api/v1/auth/profile');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/tokens');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_respects_rate_limiting_for_login()
    {
        // Attempt 6 logins (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'device_name' => 'test_device',
            ]);
        }

        // The 6th request should be rate limited
        $response->assertStatus(429);
    }
}
