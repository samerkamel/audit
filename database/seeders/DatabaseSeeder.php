<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed RBAC tables
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // Seed organizational structure
        $this->call([
            SectorSeeder::class,
            DepartmentSeeder::class,
        ]);

        // Create super admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@alfa-electronics.com',
            'password' => Hash::make('password'),
            'phone' => '+966-11-1234500',
            'mobile' => '+966-50-1234500',
            'is_active' => true,
            'language' => 'en',
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role
        $admin->assignRole('super_admin');

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Super Admin created: admin@alfa-electronics.com / password');
    }
}
