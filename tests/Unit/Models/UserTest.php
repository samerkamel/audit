<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test hasRole with single role string.
     */
    public function test_has_role_returns_true_for_assigned_role(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test hasRole returns false when role not assigned.
     */
    public function test_has_role_returns_false_when_not_assigned(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $this->assertFalse($user->hasRole('admin'));
    }

    /**
     * Test hasRole with array of roles.
     */
    public function test_has_role_with_multiple_roles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        Role::create(['name' => 'editor', 'display_name' => 'Editor']);
        $user->roles()->attach($role1->id);

        $this->assertTrue($user->hasRole(['admin', 'editor']));
        $this->assertTrue($user->hasRole(['admin', 'unknown']));
        $this->assertFalse($user->hasRole(['editor', 'unknown']));
    }

    /**
     * Test hasPermission through role.
     */
    public function test_has_permission_returns_true_when_role_has_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasPermission('create-cars'));
    }

    /**
     * Test hasPermission returns false when not granted.
     */
    public function test_has_permission_returns_false_when_not_granted(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'user', 'display_name' => 'User']);
        $user->roles()->attach($role->id);

        $this->assertFalse($user->hasPermission('create-cars'));
    }

    /**
     * Test hasAnyPermission.
     */
    public function test_has_any_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasAnyPermission(['create-cars', 'delete-cars']));
        $this->assertFalse($user->hasAnyPermission(['delete-cars', 'edit-cars']));
    }

    /**
     * Test hasAllPermissions.
     */
    public function test_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $perm1 = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $perm2 = Permission::create(['name' => 'edit-cars', 'display_name' => 'Edit CARs', 'module' => 'cars']);
        $role->permissions()->attach([$perm1->id, $perm2->id]);
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasAllPermissions(['create-cars', 'edit-cars']));
        $this->assertFalse($user->hasAllPermissions(['create-cars', 'delete-cars']));
    }

    /**
     * Test assignRole with Role model.
     */
    public function test_assign_role_with_model(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test assignRole with string.
     */
    public function test_assign_role_with_string(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test removeRole.
     */
    public function test_remove_role(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $user->roles()->attach($role->id);

        $user->removeRole($role);

        $this->assertFalse($user->hasRole('admin'));
    }

    /**
     * Test syncRoles replaces all roles.
     */
    public function test_sync_roles_replaces_all_roles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $role2 = Role::create(['name' => 'editor', 'display_name' => 'Editor']);
        $user->roles()->attach($role1->id);

        $user->syncRoles(['editor']);

        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
    }

    /**
     * Test updateLastLogin updates timestamp.
     */
    public function test_update_last_login_sets_timestamp(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $user->updateLastLogin();

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    /**
     * Test department relationship.
     */
    public function test_department_relationship(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $user->department);
        $this->assertEquals($department->id, $user->department->id);
    }

    /**
     * Test sector relationship.
     */
    public function test_sector_relationship(): void
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->assertInstanceOf(Sector::class, $user->sector);
        $this->assertEquals($sector->id, $user->sector->id);
    }

    /**
     * Test roles relationship.
     */
    public function test_roles_relationship(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $user->roles()->attach($role->id);

        $this->assertCount(1, $user->roles);
        $this->assertEquals($role->id, $user->roles->first()->id);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        User::factory()->create(['name' => 'Active User', 'is_active' => true]);
        User::factory()->create(['name' => 'Inactive User', 'is_active' => false]);

        $activeUsers = User::active()->get();

        $this->assertEquals(1, $activeUsers->count());
        $this->assertEquals('Active User', $activeUsers->first()->name);
    }

    /**
     * Test byDepartment scope.
     */
    public function test_by_department_scope(): void
    {
        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();

        User::factory()->create(['name' => 'User 1', 'department_id' => $dept1->id]);
        User::factory()->create(['name' => 'User 2', 'department_id' => $dept2->id]);

        $users = User::byDepartment($dept1->id)->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('User 1', $users->first()->name);
    }

    /**
     * Test bySector scope.
     */
    public function test_by_sector_scope(): void
    {
        $sector1 = Sector::factory()->create();
        $sector2 = Sector::factory()->create();

        User::factory()->create(['name' => 'User 1', 'sector_id' => $sector1->id]);
        User::factory()->create(['name' => 'User 2', 'sector_id' => $sector2->id]);

        $users = User::bySector($sector1->id)->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('User 1', $users->first()->name);
    }

    /**
     * Test soft deletes.
     */
    public function test_uses_soft_deletes(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();

        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
    }

    /**
     * Test datetime casting for last_login_at.
     */
    public function test_last_login_at_is_cast_correctly(): void
    {
        $user = User::factory()->create(['last_login_at' => '2025-01-15 10:30:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    /**
     * Test is_active is cast to boolean.
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $user = new User(['is_active' => 1]);
        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }
}
