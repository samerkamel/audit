<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            [
                'name' => 'Industrial Sector',
                'name_ar' => 'القطاع الصناعي',
                'code' => 'IND',
                'director_id' => null, // Will be assigned after users are created
                'description' => 'Manufacturing and production operations',
                'is_active' => true,
            ],
            [
                'name' => 'Electronics Sector',
                'name_ar' => 'قطاع الإلكترونيات',
                'code' => 'ELEC',
                'director_id' => null,
                'description' => 'Electronics design, development, and testing',
                'is_active' => true,
            ],
            [
                'name' => 'Head Office',
                'name_ar' => 'المكتب الرئيسي',
                'code' => 'HO',
                'director_id' => null,
                'description' => 'Administrative and support functions',
                'is_active' => true,
            ],
        ];

        foreach ($sectors as $sectorData) {
            Sector::create($sectorData);
        }
    }
}
