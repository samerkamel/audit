<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $fromDepartment;
    private Department $toDepartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_active' => true]);
        $this->fromDepartment = Department::factory()->create();
        $this->toDepartment = Department::factory()->create();
    }

    /**
     * Create a valid CAR for testing.
     */
    private function createValidCar(array $attributes = []): Car
    {
        $defaults = [
            'car_number' => Car::generateCarNumber(),
            'subject' => 'Test CAR Subject',
            'ncr_description' => 'Test NCR Description',
            'status' => 'draft',
            'priority' => 'medium',
            'source_type' => 'internal_audit',
            'from_department_id' => $this->fromDepartment->id,
            'to_department_id' => $this->toDepartment->id,
            'issued_date' => now(),
            'issued_by' => $this->user->id,
        ];

        return Car::create(array_merge($defaults, $attributes));
    }

    /**
     * Test index page displays CARs list.
     */
    public function test_index_displays_cars_list(): void
    {
        $car = $this->createValidCar();

        $response = $this->actingAs($this->user)->get(route('cars.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cars.index');
        $response->assertViewHas('cars');
        $response->assertViewHas('statistics');
        $response->assertSee($car->car_number);
    }

    /**
     * Test index filters by status.
     */
    public function test_index_filters_by_status(): void
    {
        $draftCar = $this->createValidCar(['status' => 'draft']);
        $issuedCar = $this->createValidCar(['status' => 'issued']);

        $response = $this->actingAs($this->user)
            ->get(route('cars.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertSee($draftCar->car_number);
    }

    /**
     * Test index requires authentication.
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('cars.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test statistics are calculated correctly.
     */
    public function test_index_statistics_are_accurate(): void
    {
        $this->createValidCar(['status' => 'draft']);
        $this->createValidCar(['status' => 'draft']);
        $this->createValidCar(['status' => 'issued']);
        $this->createValidCar(['status' => 'closed']);

        $response = $this->actingAs($this->user)->get(route('cars.index'));

        $response->assertStatus(200);
        $statistics = $response->viewData('statistics');

        $this->assertEquals(4, $statistics['total']);
        $this->assertEquals(2, $statistics['draft']);
        $this->assertEquals(1, $statistics['issued']);
        $this->assertEquals(1, $statistics['closed']);
    }

    /**
     * Test CAR model is correctly created in database.
     */
    public function test_car_can_be_created_directly(): void
    {
        $car = $this->createValidCar([
            'subject' => 'Direct Create Test',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'subject' => 'Direct Create Test',
            'priority' => 'high',
        ]);
    }

    /**
     * Test CAR can be soft deleted.
     */
    public function test_car_can_be_soft_deleted(): void
    {
        $car = $this->createValidCar(['status' => 'draft']);
        $carId = $car->id;

        $car->delete();

        $this->assertSoftDeleted('cars', ['id' => $carId]);
    }

    /**
     * Test CAR status can be updated.
     */
    public function test_car_status_can_be_updated(): void
    {
        $car = $this->createValidCar(['status' => 'draft']);

        $car->update(['status' => 'pending_approval']);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'status' => 'pending_approval',
        ]);
    }

    /**
     * Test CAR approval workflow.
     */
    public function test_car_approval_workflow(): void
    {
        $car = $this->createValidCar(['status' => 'pending_approval']);

        $car->update([
            'status' => 'issued',
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'status' => 'issued',
            'approved_by' => $this->user->id,
        ]);
    }

    /**
     * Test CAR rejection workflow.
     */
    public function test_car_rejection_workflow(): void
    {
        $car = $this->createValidCar(['status' => 'pending_approval']);

        $car->update([
            'status' => 'rejected_to_be_edited',
            'clarification' => 'Needs more details',
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'status' => 'rejected_to_be_edited',
            'clarification' => 'Needs more details',
        ]);
    }
}
