<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Car;
use App\Models\Department;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test_device')->plainTextToken;
    }

    /** @test */
    public function it_can_list_cars()
    {
        Car::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/cars');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'car_number',
                            'subject',
                            'description',
                            'source',
                            'priority',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_cars_by_status()
    {
        Car::factory()->create(['status' => 'open']);
        Car::factory()->create(['status' => 'closed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/cars?status=open');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_cars_by_priority()
    {
        Car::factory()->create(['priority' => 'high']);
        Car::factory()->create(['priority' => 'low']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/cars?priority=high');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_car()
    {
        $department = Department::factory()->create();
        $sector = Sector::factory()->create();

        $carData = [
            'car_number' => 'CAR-2025-001',
            'subject' => 'Non-conformance in welding',
            'description' => 'Welding parameters not within specified range',
            'source' => 'internal_audit',
            'priority' => 'high',
            'issued_date' => '2025-01-20',
            'due_date' => '2025-02-20',
            'assigned_to' => $this->user->id,
            'department_id' => $department->id,
            'sector_id' => $sector->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/cars', $carData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'CAR created successfully',
            ]);

        $this->assertDatabaseHas('cars', [
            'car_number' => 'CAR-2025-001',
            'priority' => 'high',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_car()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/cars', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'car_number',
                'subject',
                'description',
                'source',
                'priority',
                'issued_date',
                'due_date',
            ]);
    }

    /** @test */
    public function it_can_show_specific_car()
    {
        $car = Car::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/cars/' . $car->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $car->id,
                    'car_number' => $car->car_number,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_car()
    {
        $car = Car::factory()->create([
            'status' => 'open',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/cars/' . $car->id, [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'CAR updated successfully',
            ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_can_delete_open_car()
    {
        $car = Car::factory()->create([
            'status' => 'open',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/cars/' . $car->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'CAR deleted successfully',
            ]);

        $this->assertDatabaseMissing('cars', [
            'id' => $car->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_closed_car()
    {
        $car = Car::factory()->create([
            'status' => 'closed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/cars/' . $car->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only open or cancelled CARs can be deleted',
            ]);
    }

    /** @test */
    public function it_can_get_car_statistics()
    {
        Car::factory()->create(['status' => 'open']);
        Car::factory()->create(['status' => 'in_progress']);
        Car::factory()->count(2)->create(['status' => 'closed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/cars/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'open',
                    'in_progress',
                    'closed',
                    'overdue',
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/cars');
        $response->assertStatus(401);
    }
}
