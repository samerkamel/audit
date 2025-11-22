<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\CarFollowUp;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarFollowUpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid Car with all required fields.
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
     * Create a valid CarFollowUp with all required fields.
     */
    private function createValidFollowUp(array $attributes = []): CarFollowUp
    {
        $car = $attributes['car'] ?? $this->createValidCar();
        $user = User::factory()->create();

        $defaults = [
            'car_id' => $car->id,
            'follow_up_type' => 'correction',
            'follow_up_status' => 'pending',
            'follow_up_notes' => 'Test follow-up notes',
            'followed_up_by' => $user->id,
            'followed_up_at' => now(),
        ];

        return CarFollowUp::create(array_merge($defaults, $attributes));
    }

    /**
     * Test follow-up status color returns correct colors.
     */
    public function test_follow_up_status_color_returns_correct_colors(): void
    {
        $statusColors = [
            'accepted' => 'success',
            'not_accepted' => 'danger',
            'pending' => 'warning',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $followUp = new CarFollowUp(['follow_up_status' => $status]);
            $this->assertEquals($expectedColor, $followUp->follow_up_status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test unknown status returns secondary color.
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $followUp = new CarFollowUp(['follow_up_status' => 'unknown']);
        $this->assertEquals('secondary', $followUp->follow_up_status_color);
    }

    /**
     * Test follow-up type label for correction.
     */
    public function test_follow_up_type_label_for_correction(): void
    {
        $followUp = new CarFollowUp(['follow_up_type' => 'correction']);
        $this->assertEquals('Short-term Action', $followUp->follow_up_type_label);
    }

    /**
     * Test follow-up type label for corrective action.
     */
    public function test_follow_up_type_label_for_corrective_action(): void
    {
        $followUp = new CarFollowUp(['follow_up_type' => 'corrective_action']);
        $this->assertEquals('Long-term Action', $followUp->follow_up_type_label);
    }

    /**
     * Test follow-up type label for unknown type.
     */
    public function test_follow_up_type_label_for_unknown_type(): void
    {
        $followUp = new CarFollowUp(['follow_up_type' => 'some_other_type']);
        $this->assertEquals('Some other type', $followUp->follow_up_type_label);
    }

    /**
     * Test car relationship.
     */
    public function test_car_relationship(): void
    {
        $car = $this->createValidCar();
        $followUp = $this->createValidFollowUp(['car' => $car]);

        $this->assertInstanceOf(Car::class, $followUp->car);
        $this->assertEquals($car->id, $followUp->car->id);
    }

    /**
     * Test followedUpBy relationship.
     */
    public function test_followed_up_by_relationship(): void
    {
        $user = User::factory()->create();
        $followUp = $this->createValidFollowUp(['followed_up_by' => $user->id]);

        $this->assertInstanceOf(User::class, $followUp->followedUpBy);
        $this->assertEquals($user->id, $followUp->followedUpBy->id);
    }

    /**
     * Test datetime casting for followed_up_at.
     */
    public function test_followed_up_at_is_cast_correctly(): void
    {
        $followUp = new CarFollowUp([
            'followed_up_at' => '2025-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $followUp->followed_up_at);
    }

    /**
     * Test fillable attributes are correct.
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $followUp = new CarFollowUp();

        $expectedFillable = [
            'car_id',
            'follow_up_type',
            'follow_up_status',
            'follow_up_notes',
            'followed_up_by',
            'followed_up_at',
        ];

        $this->assertEquals($expectedFillable, $followUp->getFillable());
    }
}
