<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class, // harus sebelum SuperAdminSeeder
            RiskReferenceSeeder::class,
            DemoSeeder::class, // Hapus jika tidak ingin data demo
            SuperAdminSeeder::class,
        ]);
    }
}
