<?php

namespace Tests\Unit\Models;

use App\Models\Procedure;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcedureTest extends TestCase
{
    use RefreshDatabase;

    public function test_procedure_belongs_to_department(): void
    {
        $department = Department::factory()->create();

        $procedure = Procedure::create([
            'code' => 'QP-001',
            'name' => 'Test Procedure',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Department::class, $procedure->department);
        $this->assertEquals($department->id, $procedure->department->id);
    }

    public function test_procedure_active_scope(): void
    {
        $activeProcedure = Procedure::create([
            'code' => 'QP-001',
            'name' => 'Active Procedure',
            'is_active' => true,
        ]);

        $inactiveProcedure = Procedure::create([
            'code' => 'QP-002',
            'name' => 'Inactive Procedure',
            'is_active' => false,
        ]);

        $activeProcedures = Procedure::active()->get();

        $this->assertTrue($activeProcedures->contains($activeProcedure));
        $this->assertFalse($activeProcedures->contains($inactiveProcedure));
    }

    public function test_procedure_by_department_scope(): void
    {
        $department1 = Department::factory()->create();
        $department2 = Department::factory()->create();

        $procedure1 = Procedure::create([
            'code' => 'QP-001',
            'name' => 'Procedure 1',
            'department_id' => $department1->id,
            'is_active' => true,
        ]);

        $procedure2 = Procedure::create([
            'code' => 'QP-002',
            'name' => 'Procedure 2',
            'department_id' => $department2->id,
            'is_active' => true,
        ]);

        $dept1Procedures = Procedure::byDepartment($department1->id)->get();

        $this->assertTrue($dept1Procedures->contains($procedure1));
        $this->assertFalse($dept1Procedures->contains($procedure2));
    }

    public function test_procedure_has_unique_code(): void
    {
        Procedure::create([
            'code' => 'QP-001',
            'name' => 'Procedure 1',
            'is_active' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Procedure::create([
            'code' => 'QP-001',
            'name' => 'Procedure 2',
            'is_active' => true,
        ]);
    }

    public function test_procedure_can_have_recommended_auditors(): void
    {
        $procedure = Procedure::create([
            'code' => 'QP-001',
            'name' => 'Test Procedure',
            'is_active' => true,
        ]);

        $primaryAuditor = User::factory()->create();
        $secondaryAuditor = User::factory()->create();

        $procedure->recommendedAuditors()->attach($primaryAuditor->id, [
            'recommendation_level' => 'primary',
        ]);

        $procedure->recommendedAuditors()->attach($secondaryAuditor->id, [
            'recommendation_level' => 'secondary',
        ]);

        $this->assertCount(2, $procedure->recommendedAuditors);
        $this->assertCount(1, $procedure->primaryAuditors);
        $this->assertCount(1, $procedure->secondaryAuditors);
        $this->assertEquals($primaryAuditor->id, $procedure->primaryAuditors->first()->id);
    }
}
