<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Sector;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sectors
        $industrialSector = Sector::where('code', 'IND')->first();
        $electronicsSector = Sector::where('code', 'ELEC')->first();
        $headOffice = Sector::where('code', 'HO')->first();

        $departments = [
            // Industrial Sector Departments (5)
            [
                'sector_id' => $industrialSector->id,
                'name' => 'Production Department',
                'name_ar' => 'قسم الإنتاج',
                'code' => 'IND-PROD',
                'manager_id' => null,
                'email' => 'production@alfa-electronics.com',
                'phone' => '+966-11-1234501',
                'description' => 'Manufacturing and assembly operations',
                'is_active' => true,
            ],
            [
                'sector_id' => $industrialSector->id,
                'name' => 'Quality Control Department',
                'name_ar' => 'قسم مراقبة الجودة',
                'code' => 'IND-QC',
                'manager_id' => null,
                'email' => 'qc@alfa-electronics.com',
                'phone' => '+966-11-1234502',
                'description' => 'Product quality inspection and testing',
                'is_active' => true,
            ],
            [
                'sector_id' => $industrialSector->id,
                'name' => 'Assembly Department',
                'name_ar' => 'قسم التجميع',
                'code' => 'IND-ASM',
                'manager_id' => null,
                'email' => 'assembly@alfa-electronics.com',
                'phone' => '+966-11-1234503',
                'description' => 'Product assembly and integration',
                'is_active' => true,
            ],
            [
                'sector_id' => $industrialSector->id,
                'name' => 'Maintenance Department',
                'name_ar' => 'قسم الصيانة',
                'code' => 'IND-MAINT',
                'manager_id' => null,
                'email' => 'maintenance@alfa-electronics.com',
                'phone' => '+966-11-1234504',
                'description' => 'Equipment and facility maintenance',
                'is_active' => true,
            ],
            [
                'sector_id' => $industrialSector->id,
                'name' => 'Warehouse Department',
                'name_ar' => 'قسم المستودعات',
                'code' => 'IND-WH',
                'manager_id' => null,
                'email' => 'warehouse@alfa-electronics.com',
                'phone' => '+966-11-1234505',
                'description' => 'Inventory and logistics management',
                'is_active' => true,
            ],

            // Electronics Sector Departments (5)
            [
                'sector_id' => $electronicsSector->id,
                'name' => 'R&D Department',
                'name_ar' => 'قسم البحث والتطوير',
                'code' => 'ELEC-RD',
                'manager_id' => null,
                'email' => 'rd@alfa-electronics.com',
                'phone' => '+966-11-1234506',
                'description' => 'Research and development activities',
                'is_active' => true,
            ],
            [
                'sector_id' => $electronicsSector->id,
                'name' => 'Electronics Engineering Department',
                'name_ar' => 'قسم الهندسة الإلكترونية',
                'code' => 'ELEC-ENG',
                'manager_id' => null,
                'email' => 'engineering@alfa-electronics.com',
                'phone' => '+966-11-1234507',
                'description' => 'Electronics design and engineering',
                'is_active' => true,
            ],
            [
                'sector_id' => $electronicsSector->id,
                'name' => 'PCB Design Department',
                'name_ar' => 'قسم تصميم الدوائر المطبوعة',
                'code' => 'ELEC-PCB',
                'manager_id' => null,
                'email' => 'pcb@alfa-electronics.com',
                'phone' => '+966-11-1234508',
                'description' => 'Printed circuit board design',
                'is_active' => true,
            ],
            [
                'sector_id' => $electronicsSector->id,
                'name' => 'Testing & Validation Department',
                'name_ar' => 'قسم الاختبار والتحقق',
                'code' => 'ELEC-TEST',
                'manager_id' => null,
                'email' => 'testing@alfa-electronics.com',
                'phone' => '+966-11-1234509',
                'description' => 'Product testing and validation',
                'is_active' => true,
            ],
            [
                'sector_id' => $electronicsSector->id,
                'name' => 'Product Development Department',
                'name_ar' => 'قسم تطوير المنتجات',
                'code' => 'ELEC-PD',
                'manager_id' => null,
                'email' => 'product-dev@alfa-electronics.com',
                'phone' => '+966-11-1234510',
                'description' => 'New product development',
                'is_active' => true,
            ],

            // Head Office Departments (4)
            [
                'sector_id' => $headOffice->id,
                'name' => 'Finance Department',
                'name_ar' => 'قسم المالية',
                'code' => 'HO-FIN',
                'manager_id' => null,
                'email' => 'finance@alfa-electronics.com',
                'phone' => '+966-11-1234511',
                'description' => 'Financial management and accounting',
                'is_active' => true,
            ],
            [
                'sector_id' => $headOffice->id,
                'name' => 'Human Resources Department',
                'name_ar' => 'قسم الموارد البشرية',
                'code' => 'HO-HR',
                'manager_id' => null,
                'email' => 'hr@alfa-electronics.com',
                'phone' => '+966-11-1234512',
                'description' => 'Human resources and personnel management',
                'is_active' => true,
            ],
            [
                'sector_id' => $headOffice->id,
                'name' => 'IT Department',
                'name_ar' => 'قسم تقنية المعلومات',
                'code' => 'HO-IT',
                'manager_id' => null,
                'email' => 'it@alfa-electronics.com',
                'phone' => '+966-11-1234513',
                'description' => 'Information technology and systems',
                'is_active' => true,
            ],
            [
                'sector_id' => $headOffice->id,
                'name' => 'Procurement Department',
                'name_ar' => 'قسم المشتريات',
                'code' => 'HO-PROC',
                'manager_id' => null,
                'email' => 'procurement@alfa-electronics.com',
                'phone' => '+966-11-1234514',
                'description' => 'Procurement and supplier management',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }
    }
}
