<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun Super Admin awal (tanpa satker, lintas satker).
 * Set SEED_ADMIN_PASSWORD di .env, lalu GANTI password setelah login pertama.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => env('SEED_ADMIN_EMAIL', 'superadmin@simris.local'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'ChangeMe123!')),
                'is_active' => true,
                'satker_id' => null,
                'bidang_id' => null,
                'sub_bidang_id' => null,
            ]
        );

        $user->syncRoles(['super_admin']);
    }
}
