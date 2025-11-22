<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid CAR with all required fields.
     */
    private function createValidCar(array $attributes = []): Car
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();

        $defaults = [
            'car_number' => $attributes['car_number'] ?? Car::generateCarNumber(),
            'subject' => 'Test Subject',
            'ncr_description' => 'Test NCR Description',
            'status' => 'draft',
            'priority' => 'medium',
            'source_type' => 'internal_audit',
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'issued_date' => now(),
            'issued_by' => $user->id,
        ];

        return Car::create(array_merge($defaults, $attributes));
    }

    /**
     * Test CAR number generation produces correct format (CYY###)
     */
    public function test_generate_car_number_returns_correct_format(): void
    {
        $carNumber = Car::generateCarNumber();

        $this->assertMatchesRegularExpression('/^C\d{2}\d{3}$/', $carNumber);
        $this->assertStringStartsWith('C' . date('y'), $carNumber);
    }

    /**
     * Test CAR number generation increments correctly
     */
    public function test_generate_car_number_increments_sequentially(): void
    {
        $car1 = $this->createValidCar();

        $carNumber2 = Car::generateCarNumber();

        $num1 = (int) substr($car1->car_number, 3);
        $num2 = (int) substr($carNumber2, 3);

        $this->assertEquals($num1 + 1, $num2);
    }

    /**
     * Test CAR number generation handles soft-deleted records
     */
    public function test_generate_car_number_includes_soft_deleted(): void
    {
        $car = $this->createValidCar();
        $deletedNumber = $car->car_number;
        $car->delete();

        $newNumber = Car::generateCarNumber();

        $this->assertNotEquals($deletedNumber, $newNumber);

        $deletedNum = (int) substr($deletedNumber, 3);
        $newNum = (int) substr($newNumber, 3);
        $this->assertGreaterThan($deletedNum, $newNum);
    }

    /**
     * Test CAR has correct status colors
     */
    public function test_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'draft' => 'secondary',
            'pending_approval' => 'info',
            'issued' => 'primary',
            'in_progress' => 'warning',
            'pending_review' => 'info',
            'rejected_to_be_edited' => 'danger',
            'closed' => 'success',
            'late' => 'danger',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $car = new Car(['status' => $status]);
            $this->assertEquals($expectedColor, $car->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test CAR has correct priority colors
     */
    public function test_priority_color_attribute_returns_correct_colors(): void
    {
        $priorityColors = [
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
        ];

        foreach ($priorityColors as $priority => $expectedColor) {
            $car = new Car(['priority' => $priority]);
            $this->assertEquals($expectedColor, $car->priority_color, "Failed for priority: {$priority}");
        }
    }

    /**
     * Test CAR fillable attributes are set correctly
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $car = new Car([
            'car_number' => 'C25001',
            'subject' => 'Test Subject',
            'ncr_description' => 'Test Description',
            'status' => 'draft',
            'priority' => 'high',
        ]);

        $this->assertEquals('C25001', $car->car_number);
        $this->assertEquals('Test Subject', $car->subject);
        $this->assertEquals('Test Description', $car->ncr_description);
        $this->assertEquals('draft', $car->status);
        $this->assertEquals('high', $car->priority);
    }

    /**
     * Test CAR date casting
     */
    public function test_dates_are_cast_correctly(): void
    {
        $car = new Car([
            'issued_date' => '2025-01-15',
            'approved_at' => '2025-01-16 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $car->issued_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $car->approved_at);
    }

    /**
     * Test CAR soft deletes
     */
    public function test_car_uses_soft_deletes(): void
    {
        $car = $this->createValidCar();
        $carId = $car->id;
        $car->delete();

        $this->assertNull(Car::find($carId));
        $this->assertNotNull(Car::withTrashed()->find($carId));
    }

    /**
     * Test CAR belongs to fromDepartment
     */
    public function test_from_department_relationship(): void
    {
        $department = Department::factory()->create();
        $car = $this->createValidCar(['from_department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $car->fromDepartment);
        $this->assertEquals($department->id, $car->fromDepartment->id);
    }

    /**
     * Test CAR belongs to toDepartment
     */
    public function test_to_department_relationship(): void
    {
        $department = Department::factory()->create();
        $car = $this->createValidCar(['to_department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $car->toDepartment);
        $this->assertEquals($department->id, $car->toDepartment->id);
    }

    /**
     * Test CAR belongs to issuedBy user
     */
    public function test_issued_by_relationship(): void
    {
        $user = User::factory()->create();
        $car = $this->createValidCar(['issued_by' => $user->id]);

        $this->assertInstanceOf(User::class, $car->issuedBy);
        $this->assertEquals($user->id, $car->issuedBy->id);
    }

    /**
     * Test CAR byStatus scope
     */
    public function test_by_status_scope(): void
    {
        $this->createValidCar(['status' => 'draft']);
        $this->createValidCar(['status' => 'draft']);
        $this->createValidCar(['status' => 'issued']);

        $draftCars = Car::byStatus('draft')->get();
        $issuedCars = Car::byStatus('issued')->get();

        $this->assertEquals(2, $draftCars->count());
        $this->assertEquals(1, $issuedCars->count());
    }

    /**
     * Test CAR byDepartment scope
     */
    public function test_by_department_scope(): void
    {
        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();

        $this->createValidCar(['to_department_id' => $dept1->id]);
        $this->createValidCar(['to_department_id' => $dept1->id]);
        $this->createValidCar(['to_department_id' => $dept2->id]);

        $dept1Cars = Car::byDepartment($dept1->id)->get();
        $dept2Cars = Car::byDepartment($dept2->id)->get();

        $this->assertEquals(2, $dept1Cars->count());
        $this->assertEquals(1, $dept2Cars->count());
    }

    /**
     * Test CAR bySourceType scope
     */
    public function test_by_source_type_scope(): void
    {
        $this->createValidCar(['source_type' => 'internal_audit']);
        $this->createValidCar(['source_type' => 'internal_audit']);
        $this->createValidCar(['source_type' => 'external_audit']);

        $internalCars = Car::bySourceType('internal_audit')->get();
        $externalCars = Car::bySourceType('external_audit')->get();

        $this->assertEquals(2, $internalCars->count());
        $this->assertEquals(1, $externalCars->count());
    }

    /**
     * Test unknown status returns default color
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $car = new Car(['status' => 'unknown_status']);
        $this->assertEquals('secondary', $car->status_color);
    }

    /**
     * Test unknown priority returns default color
     */
    public function test_unknown_priority_returns_secondary_color(): void
    {
        $car = new Car(['priority' => 'unknown_priority']);
        $this->assertEquals('secondary', $car->priority_color);
    }
}
