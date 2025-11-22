<?php

namespace Tests\Unit\Models;

use App\Models\Car;
use App\Models\CarResponse;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarResponseTest extends TestCase
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
     * Create a valid CarResponse with all required fields.
     */
    private function createValidResponse(array $attributes = []): CarResponse
    {
        $car = $attributes['car'] ?? $this->createValidCar();
        $user = User::factory()->create();

        $defaults = [
            'car_id' => $car->id,
            'root_cause' => 'Test root cause',
            'correction' => 'Test correction',
            'correction_target_date' => now()->addDays(7),
            'corrective_action' => 'Test corrective action',
            'corrective_action_target_date' => now()->addDays(14),
            'response_status' => 'pending',
            'responded_by' => $user->id,
            'responded_at' => now(),
        ];

        return CarResponse::create(array_merge($defaults, $attributes));
    }

    /**
     * Test correction overdue returns true when target passed without actual date.
     */
    public function test_is_correction_overdue_returns_true_when_target_passed(): void
    {
        $response = new CarResponse([
            'correction_target_date' => now()->subDay(),
            'correction_actual_date' => null,
        ]);

        $this->assertTrue($response->isCorrectionOverdue());
    }

    /**
     * Test correction overdue returns false when actual date is set.
     */
    public function test_is_correction_overdue_returns_false_when_completed(): void
    {
        $response = new CarResponse([
            'correction_target_date' => now()->subDay(),
            'correction_actual_date' => now()->subDays(2),
        ]);

        $this->assertFalse($response->isCorrectionOverdue());
    }

    /**
     * Test correction overdue returns false when target not yet passed.
     */
    public function test_is_correction_overdue_returns_false_when_not_due(): void
    {
        $response = new CarResponse([
            'correction_target_date' => now()->addDay(),
            'correction_actual_date' => null,
        ]);

        $this->assertFalse($response->isCorrectionOverdue());
    }

    /**
     * Test corrective action overdue returns true when target passed.
     */
    public function test_is_corrective_action_overdue_returns_true_when_target_passed(): void
    {
        $response = new CarResponse([
            'corrective_action_target_date' => now()->subDay(),
            'corrective_action_actual_date' => null,
        ]);

        $this->assertTrue($response->isCorrectiveActionOverdue());
    }

    /**
     * Test corrective action overdue returns false when completed.
     */
    public function test_is_corrective_action_overdue_returns_false_when_completed(): void
    {
        $response = new CarResponse([
            'corrective_action_target_date' => now()->subDay(),
            'corrective_action_actual_date' => now()->subDays(2),
        ]);

        $this->assertFalse($response->isCorrectiveActionOverdue());
    }

    /**
     * Test isComplete returns true when both dates are set.
     */
    public function test_is_complete_returns_true_when_both_dates_set(): void
    {
        $response = new CarResponse([
            'correction_actual_date' => now()->subDays(2),
            'corrective_action_actual_date' => now()->subDay(),
        ]);

        $this->assertTrue($response->isComplete());
    }

    /**
     * Test isComplete returns false when correction date missing.
     */
    public function test_is_complete_returns_false_when_correction_missing(): void
    {
        $response = new CarResponse([
            'correction_actual_date' => null,
            'corrective_action_actual_date' => now()->subDay(),
        ]);

        $this->assertFalse($response->isComplete());
    }

    /**
     * Test isComplete returns false when corrective action date missing.
     */
    public function test_is_complete_returns_false_when_corrective_action_missing(): void
    {
        $response = new CarResponse([
            'correction_actual_date' => now()->subDay(),
            'corrective_action_actual_date' => null,
        ]);

        $this->assertFalse($response->isComplete());
    }

    /**
     * Test isComplete returns false when both dates missing.
     */
    public function test_is_complete_returns_false_when_both_dates_missing(): void
    {
        $response = new CarResponse([
            'correction_actual_date' => null,
            'corrective_action_actual_date' => null,
        ]);

        $this->assertFalse($response->isComplete());
    }

    /**
     * Test response status color attribute returns correct colors.
     */
    public function test_response_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'pending' => 'secondary',
            'submitted' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $response = new CarResponse(['response_status' => $status]);
            $this->assertEquals($expectedColor, $response->response_status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test unknown status returns secondary color.
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $response = new CarResponse(['response_status' => 'unknown']);
        $this->assertEquals('secondary', $response->response_status_color);
    }

    /**
     * Test car relationship.
     */
    public function test_car_relationship(): void
    {
        $car = $this->createValidCar();
        $response = $this->createValidResponse(['car' => $car]);

        $this->assertInstanceOf(Car::class, $response->car);
        $this->assertEquals($car->id, $response->car->id);
    }

    /**
     * Test respondedBy relationship.
     */
    public function test_responded_by_relationship(): void
    {
        $user = User::factory()->create();
        $response = $this->createValidResponse(['responded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $response->respondedBy);
        $this->assertEquals($user->id, $response->respondedBy->id);
    }

    /**
     * Test reviewedBy relationship.
     */
    public function test_reviewed_by_relationship(): void
    {
        $user = User::factory()->create();
        $response = $this->createValidResponse(['reviewed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $response->reviewedBy);
        $this->assertEquals($user->id, $response->reviewedBy->id);
    }

    /**
     * Test date casting for correction dates.
     */
    public function test_correction_dates_are_cast_correctly(): void
    {
        $response = new CarResponse([
            'correction_target_date' => '2025-01-15',
            'correction_actual_date' => '2025-01-10',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $response->correction_target_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $response->correction_actual_date);
    }

    /**
     * Test date casting for corrective action dates.
     */
    public function test_corrective_action_dates_are_cast_correctly(): void
    {
        $response = new CarResponse([
            'corrective_action_target_date' => '2025-01-20',
            'corrective_action_actual_date' => '2025-01-18',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $response->corrective_action_target_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $response->corrective_action_actual_date);
    }

    /**
     * Test datetime casting for responded_at.
     */
    public function test_responded_at_is_cast_correctly(): void
    {
        $response = new CarResponse([
            'responded_at' => '2025-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $response->responded_at);
    }

    /**
     * Test attachments are cast to array.
     */
    public function test_attachments_cast_to_array(): void
    {
        $response = $this->createValidResponse([
            'attachments' => ['file1.pdf', 'file2.pdf'],
        ]);

        $response = $response->fresh();

        $this->assertIsArray($response->attachments);
        $this->assertCount(2, $response->attachments);
    }

    /**
     * Test fillable attributes are correct.
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $response = new CarResponse();

        $expectedFillable = [
            'car_id',
            'root_cause',
            'correction',
            'correction_target_date',
            'correction_actual_date',
            'corrective_action',
            'corrective_action_target_date',
            'corrective_action_actual_date',
            'attachments',
            'response_status',
            'responded_by',
            'responded_at',
            'reviewed_by',
            'reviewed_at',
            'rejection_reason',
        ];

        $this->assertEquals($expectedFillable, $response->getFillable());
    }
}
