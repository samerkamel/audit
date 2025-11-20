<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Complete system access for technical configuration and maintenance',
                'is_system_role' => true,
            ],
            [
                'name' => 'quality_manager',
                'display_name' => 'Quality Manager',
                'description' => 'Oversee all audit activities, approve reports and CARs',
                'is_system_role' => false,
            ],
            [
                'name' => 'quality_engineer',
                'display_name' => 'Quality Engineer / Auditor',
                'description' => 'Conduct audits, create reports, issue CARs',
                'is_system_role' => false,
            ],
            [
                'name' => 'sector_director',
                'display_name' => 'Sector Director',
                'description' => 'Oversee sector-level audit activities and approve responses',
                'is_system_role' => false,
            ],
            [
                'name' => 'department_manager',
                'display_name' => 'Department Manager',
                'description' => 'Respond to audits, CARs, and complaints for their department',
                'is_system_role' => false,
            ],
            [
                'name' => 'management_rep',
                'display_name' => 'Management Representative',
                'description' => 'Strategic oversight, view reports and analytics',
                'is_system_role' => false,
            ],
            [
                'name' => 'external_auditor',
                'display_name' => 'External Auditor (Read-Only)',
                'description' => 'View assigned audit documentation only',
                'is_system_role' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}
