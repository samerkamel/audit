<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test hasPermission returns true when permission exists.
     */
    public function test_has_permission_returns_true_when_exists(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);

        $this->assertTrue($role->hasPermission('create-cars'));
    }

    /**
     * Test hasPermission returns false when permission not assigned.
     */
    public function test_has_permission_returns_false_when_not_assigned(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $this->assertFalse($role->hasPermission('create-cars'));
    }

    /**
     * Test givePermission with Permission model.
     */
    public function test_give_permission_with_model(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);

        $role->givePermission($permission);

        $this->assertTrue($role->hasPermission('create-cars'));
    }

    /**
     * Test givePermission with string.
     */
    public function test_give_permission_with_string(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);

        $role->givePermission('create-cars');

        $this->assertTrue($role->hasPermission('create-cars'));
    }

    /**
     * Test revokePermission removes permission.
     */
    public function test_revoke_permission_removes_permission(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);

        $role->revokePermission($permission);

        $this->assertFalse($role->hasPermission('create-cars'));
    }

    /**
     * Test revokePermission with string.
     */
    public function test_revoke_permission_with_string(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);

        $role->revokePermission('create-cars');

        $this->assertFalse($role->hasPermission('create-cars'));
    }

    /**
     * Test users relationship.
     */
    public function test_users_relationship(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $user = User::factory()->create();
        $role->users()->attach($user->id);

        $this->assertCount(1, $role->users);
        $this->assertEquals($user->id, $role->users->first()->id);
    }

    /**
     * Test permissions relationship.
     */
    public function test_permissions_relationship(): void
    {
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-cars', 'display_name' => 'Create CARs', 'module' => 'cars']);
        $role->permissions()->attach($permission->id);

        $this->assertCount(1, $role->permissions);
        $this->assertEquals($permission->id, $role->permissions->first()->id);
    }

    /**
     * Test is_system_role is cast to boolean.
     */
    public function test_is_system_role_is_cast_to_boolean(): void
    {
        $role = new Role(['is_system_role' => 1]);
        $this->assertIsBool($role->is_system_role);
        $this->assertTrue($role->is_system_role);

        $role = new Role(['is_system_role' => 0]);
        $this->assertFalse($role->is_system_role);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes_are_set_correctly(): void
    {
        $role = new Role();

        $expectedFillable = [
            'name',
            'display_name',
            'description',
            'is_system_role',
        ];

        $this->assertEquals($expectedFillable, $role->getFillable());
    }
}
