<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sector relationship.
     */
    public function test_sector_relationship(): void
    {
        $sector = Sector::factory()->create();
        $department = Department::factory()->create(['sector_id' => $sector->id]);

        $this->assertInstanceOf(Sector::class, $department->sector);
        $this->assertEquals($sector->id, $department->sector->id);
    }

    /**
     * Test manager relationship.
     */
    public function test_manager_relationship(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create(['manager_id' => $user->id]);

        $this->assertInstanceOf(User::class, $department->manager);
        $this->assertEquals($user->id, $department->manager->id);
    }

    /**
     * Test users relationship.
     */
    public function test_users_relationship(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertCount(1, $department->users);
        $this->assertEquals($user->id, $department->users->first()->id);
    }

    /**
     * Test activeUsers relationship.
     */
    public function test_active_users_relationship(): void
    {
        $department = Department::factory()->create();
        User::factory()->create(['department_id' => $department->id, 'is_active' => true]);
        User::factory()->create(['department_id' => $department->id, 'is_active' => false]);

        $this->assertCount(1, $department->activeUsers);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        Department::factory()->create(['name' => 'Active Dept', 'is_active' => true]);
        Department::factory()->create(['name' => 'Inactive Dept', 'is_active' => false]);

        $activeDepts = Department::active()->get();

        $this->assertEquals(1, $activeDepts->count());
        $this->assertEquals('Active Dept', $activeDepts->first()->name);
    }

    /**
     * Test bySector scope.
     */
    public function test_by_sector_scope(): void
    {
        $sector1 = Sector::factory()->create();
        $sector2 = Sector::factory()->create();

        Department::factory()->create(['name' => 'Dept 1', 'sector_id' => $sector1->id]);
        Department::factory()->create(['name' => 'Dept 2', 'sector_id' => $sector2->id]);

        $depts = Department::bySector($sector1->id)->get();

        $this->assertEquals(1, $depts->count());
        $this->assertEquals('Dept 1', $depts->first()->name);
    }

    /**
     * Test is_active is cast to boolean.
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $department = new Department(['is_active' => 1]);
        $this->assertIsBool($department->is_active);
        $this->assertTrue($department->is_active);

        $department = new Department(['is_active' => 0]);
        $this->assertFalse($department->is_active);
    }

    /**
     * Test soft deletes.
     */
    public function test_uses_soft_deletes(): void
    {
        $department = Department::factory()->create();
        $deptId = $department->id;
        $department->delete();

        $this->assertNull(Department::find($deptId));
        $this->assertNotNull(Department::withTrashed()->find($deptId));
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $department = new Department();

        $expectedFillable = [
            'sector_id',
            'name',
            'name_ar',
            'code',
            'manager_id',
            'email',
            'phone',
            'description',
            'is_active',
        ];

        $this->assertEquals($expectedFillable, $department->getFillable());
    }
}
