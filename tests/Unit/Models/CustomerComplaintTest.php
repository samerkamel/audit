<?php

namespace Tests\Unit\Models;

use App\Models\CustomerComplaint;
use App\Models\Car;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerComplaintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid CustomerComplaint with all required fields.
     */
    private function createValidComplaint(array $attributes = []): CustomerComplaint
    {
        $defaults = [
            'complaint_number' => $attributes['complaint_number'] ?? CustomerComplaint::generateComplaintNumber(),
            'complaint_date' => now(),
            'customer_name' => 'Test Customer',
            'complaint_subject' => 'Test Subject',
            'complaint_description' => 'Test complaint description',
            'status' => 'new',
        ];

        return CustomerComplaint::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a valid CAR with all required fields.
     */
    private function createValidCar(array $attributes = []): Car
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();

        $defaults = [
            'car_number' => Car::generateCarNumber(),
            'subject' => 'Test Subject',
            'ncr_description' => 'Test NCR Description',
            'status' => 'draft',
            'priority' => 'medium',
            'source_type' => 'customer_complaint',
            'from_department_id' => $department->id,
            'to_department_id' => $department->id,
            'issued_date' => now(),
            'issued_by' => $user->id,
        ];

        return Car::create(array_merge($defaults, $attributes));
    }

    /**
     * Test complaint number generation produces correct format (COMP-YY-####)
     */
    public function test_generate_complaint_number_returns_correct_format(): void
    {
        $complaintNumber = CustomerComplaint::generateComplaintNumber();

        $this->assertMatchesRegularExpression('/^COMP-\d{2}-\d{4}$/', $complaintNumber);
        $this->assertStringStartsWith('COMP-' . date('y') . '-', $complaintNumber);
    }

    /**
     * Test complaint number generation increments correctly
     */
    public function test_generate_complaint_number_increments_sequentially(): void
    {
        $complaint1 = $this->createValidComplaint();
        $complaintNumber2 = CustomerComplaint::generateComplaintNumber();

        $num1 = (int) substr($complaint1->complaint_number, -4);
        $num2 = (int) substr($complaintNumber2, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    /**
     * Test complaint number generation handles soft-deleted records
     */
    public function test_generate_complaint_number_includes_soft_deleted(): void
    {
        $complaint = $this->createValidComplaint();
        $deletedNumber = $complaint->complaint_number;
        $complaint->delete();

        $newNumber = CustomerComplaint::generateComplaintNumber();

        $this->assertNotEquals($deletedNumber, $newNumber);

        $deletedNum = (int) substr($deletedNumber, -4);
        $newNum = (int) substr($newNumber, -4);
        $this->assertGreaterThan($deletedNum, $newNum);
    }

    /**
     * Test status color accessor returns correct values
     */
    public function test_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'new' => 'primary',
            'acknowledged' => 'info',
            'investigating' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'escalated' => 'danger',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $complaint = new CustomerComplaint(['status' => $status]);
            $this->assertEquals($expectedColor, $complaint->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test priority color accessor returns correct values
     */
    public function test_priority_color_attribute_returns_correct_colors(): void
    {
        $priorityColors = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
        ];

        foreach ($priorityColors as $priority => $expectedColor) {
            $complaint = new CustomerComplaint(['priority' => $priority]);
            $this->assertEquals($expectedColor, $complaint->priority_color, "Failed for priority: {$priority}");
        }
    }

    /**
     * Test severity color accessor returns correct values
     */
    public function test_severity_color_attribute_returns_correct_colors(): void
    {
        $severityColors = [
            'minor' => 'info',
            'major' => 'warning',
            'critical' => 'danger',
        ];

        foreach ($severityColors as $severity => $expectedColor) {
            $complaint = new CustomerComplaint(['severity' => $severity]);
            $this->assertEquals($expectedColor, $complaint->severity_color, "Failed for severity: {$severity}");
        }
    }

    /**
     * Test category label accessor
     */
    public function test_category_label_attribute(): void
    {
        $categoryLabels = [
            'product_quality' => 'Product Quality',
            'service_quality' => 'Service Quality',
            'delivery' => 'Delivery',
            'documentation' => 'Documentation',
            'technical_support' => 'Technical Support',
            'billing' => 'Billing',
            'other' => 'Other',
        ];

        foreach ($categoryLabels as $category => $expectedLabel) {
            $complaint = new CustomerComplaint(['complaint_category' => $category]);
            $this->assertEquals($expectedLabel, $complaint->category_label, "Failed for category: {$category}");
        }
    }

    /**
     * Test isOverdue returns true when past response date and not resolved
     */
    public function test_is_overdue_returns_true_when_past_response_date(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'investigating',
            'response_date' => now()->subDay(),
        ]);

        $this->assertTrue($complaint->isOverdue());
    }

    /**
     * Test isOverdue returns false when closed
     */
    public function test_is_overdue_returns_false_when_closed(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'closed',
            'response_date' => now()->subDay(),
        ]);

        $this->assertFalse($complaint->isOverdue());
    }

    /**
     * Test isOverdue returns false when resolved
     */
    public function test_is_overdue_returns_false_when_resolved(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'resolved',
            'response_date' => now()->subDay(),
        ]);

        $this->assertFalse($complaint->isOverdue());
    }

    /**
     * Test isOverdue returns false when no response date
     */
    public function test_is_overdue_returns_false_when_no_response_date(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'investigating',
            'response_date' => null,
        ]);

        $this->assertFalse($complaint->isOverdue());
    }

    /**
     * Test canGenerateCar returns true when conditions met
     */
    public function test_can_generate_car_returns_true_when_conditions_met(): void
    {
        $complaint = new CustomerComplaint([
            'car_required' => true,
            'car_id' => null,
            'status' => 'investigating',
        ]);

        $this->assertTrue($complaint->canGenerateCar());
    }

    /**
     * Test canGenerateCar returns false when CAR not required
     */
    public function test_can_generate_car_returns_false_when_not_required(): void
    {
        $complaint = new CustomerComplaint([
            'car_required' => false,
            'car_id' => null,
            'status' => 'investigating',
        ]);

        $this->assertFalse($complaint->canGenerateCar());
    }

    /**
     * Test canGenerateCar returns false when CAR already exists
     */
    public function test_can_generate_car_returns_false_when_car_exists(): void
    {
        $complaint = new CustomerComplaint([
            'car_required' => true,
            'car_id' => 1,
            'status' => 'investigating',
        ]);

        $this->assertFalse($complaint->canGenerateCar());
    }

    /**
     * Test canGenerateCar returns false for wrong status
     */
    public function test_can_generate_car_returns_false_for_wrong_status(): void
    {
        $complaint = new CustomerComplaint([
            'car_required' => true,
            'car_id' => null,
            'status' => 'new',
        ]);

        $this->assertFalse($complaint->canGenerateCar());
    }

    /**
     * Test canBeClosed returns true when resolved with CAR completed
     */
    public function test_can_be_closed_returns_true_when_resolved_with_car(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'resolved',
            'car_required' => true,
            'car_id' => 1,
        ]);

        $this->assertTrue($complaint->canBeClosed());
    }

    /**
     * Test canBeClosed returns true when resolved without CAR requirement
     */
    public function test_can_be_closed_returns_true_when_no_car_required(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'resolved',
            'car_required' => false,
            'car_id' => null,
        ]);

        $this->assertTrue($complaint->canBeClosed());
    }

    /**
     * Test canBeClosed returns false when not resolved
     */
    public function test_can_be_closed_returns_false_when_not_resolved(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'investigating',
            'car_required' => false,
        ]);

        $this->assertFalse($complaint->canBeClosed());
    }

    /**
     * Test canBeClosed returns false when CAR required but missing
     */
    public function test_can_be_closed_returns_false_when_car_missing(): void
    {
        $complaint = new CustomerComplaint([
            'status' => 'resolved',
            'car_required' => true,
            'car_id' => null,
        ]);

        $this->assertFalse($complaint->canBeClosed());
    }

    /**
     * Test assignedToDepartment relationship
     */
    public function test_assigned_to_department_relationship(): void
    {
        $department = Department::factory()->create();
        $complaint = $this->createValidComplaint(['assigned_to_department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $complaint->assignedToDepartment);
        $this->assertEquals($department->id, $complaint->assignedToDepartment->id);
    }

    /**
     * Test assignedToUser relationship
     */
    public function test_assigned_to_user_relationship(): void
    {
        $user = User::factory()->create();
        $complaint = $this->createValidComplaint(['assigned_to_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $complaint->assignedToUser);
        $this->assertEquals($user->id, $complaint->assignedToUser->id);
    }

    /**
     * Test car relationship
     */
    public function test_car_relationship(): void
    {
        $car = $this->createValidCar();
        $complaint = $this->createValidComplaint([
            'status' => 'investigating',
            'car_id' => $car->id,
        ]);

        $this->assertInstanceOf(Car::class, $complaint->car);
        $this->assertEquals($car->id, $complaint->car->id);
    }

    /**
     * Test soft deletes
     */
    public function test_uses_soft_deletes(): void
    {
        $complaint = $this->createValidComplaint();
        $complaintId = $complaint->id;
        $complaint->delete();

        $this->assertNull(CustomerComplaint::find($complaintId));
        $this->assertNotNull(CustomerComplaint::withTrashed()->find($complaintId));
    }

    /**
     * Test date casting
     */
    public function test_dates_are_cast_correctly(): void
    {
        $complaint = new CustomerComplaint([
            'complaint_date' => '2025-01-15',
            'response_date' => '2025-01-16',
            'resolved_date' => '2025-01-20',
            'closed_at' => '2025-01-21 10:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $complaint->complaint_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $complaint->response_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $complaint->resolved_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $complaint->closed_at);
    }

    /**
     * Test boolean casting for car_required
     */
    public function test_car_required_is_cast_to_boolean(): void
    {
        $complaint = new CustomerComplaint(['car_required' => 1]);
        $this->assertTrue($complaint->car_required);

        $complaint = new CustomerComplaint(['car_required' => 0]);
        $this->assertFalse($complaint->car_required);
    }

    /**
     * Test unknown status returns default color
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $complaint = new CustomerComplaint(['status' => 'unknown']);
        $this->assertEquals('secondary', $complaint->status_color);
    }

    /**
     * Test unknown priority returns default color
     */
    public function test_unknown_priority_returns_secondary_color(): void
    {
        $complaint = new CustomerComplaint(['priority' => 'unknown']);
        $this->assertEquals('secondary', $complaint->priority_color);
    }

    /**
     * Test unknown severity returns default color
     */
    public function test_unknown_severity_returns_secondary_color(): void
    {
        $complaint = new CustomerComplaint(['severity' => 'unknown']);
        $this->assertEquals('secondary', $complaint->severity_color);
    }
}
