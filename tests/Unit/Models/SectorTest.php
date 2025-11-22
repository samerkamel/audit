<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test director relationship.
     */
    public function test_director_relationship(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create(['director_id' => $user->id]);

        $this->assertInstanceOf(User::class, $sector->director);
        $this->assertEquals($user->id, $sector->director->id);
    }

    /**
     * Test departments relationship.
     */
    public function test_departments_relationship(): void
    {
        $sector = Sector::factory()->create();
        Department::factory()->create(['sector_id' => $sector->id]);
        Department::factory()->create(['sector_id' => $sector->id]);

        $this->assertCount(2, $sector->departments);
    }

    /**
     * Test users relationship.
     */
    public function test_users_relationship(): void
    {
        $sector = Sector::factory()->create();
        User::factory()->create(['sector_id' => $sector->id]);

        $this->assertCount(1, $sector->users);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        Sector::factory()->create(['name' => 'Active Sector', 'is_active' => true]);
        Sector::factory()->create(['name' => 'Inactive Sector', 'is_active' => false]);

        $activeSectors = Sector::active()->get();

        $this->assertEquals(1, $activeSectors->count());
        $this->assertEquals('Active Sector', $activeSectors->first()->name);
    }

    /**
     * Test is_active is cast to boolean.
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $sector = new Sector(['is_active' => 1]);
        $this->assertIsBool($sector->is_active);
        $this->assertTrue($sector->is_active);

        $sector = new Sector(['is_active' => 0]);
        $this->assertFalse($sector->is_active);
    }

    /**
     * Test soft deletes.
     */
    public function test_uses_soft_deletes(): void
    {
        $sector = Sector::factory()->create();
        $sectorId = $sector->id;
        $sector->delete();

        $this->assertNull(Sector::find($sectorId));
        $this->assertNotNull(Sector::withTrashed()->find($sectorId));
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $sector = new Sector();

        $expectedFillable = [
            'name',
            'name_ar',
            'code',
            'director_id',
            'description',
            'is_active',
        ];

        $this->assertEquals($expectedFillable, $sector->getFillable());
    }
}
