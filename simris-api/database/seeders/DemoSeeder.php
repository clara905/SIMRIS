<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Bidang;
use App\Models\Satker;
use App\Models\SubBidang;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $satker = Satker::firstOrCreate(
            ['kode_satker' => 'SATKER-01'],
            ['nama_satker' => 'Satker Contoh'],
        );
        $bidang = Bidang::firstOrCreate(
            ['satker_id' => $satker->satker_id, 'kode_bidang' => 'BID-TI'],
            ['nama_bidang' => 'Bidang Teknologi Informasi'],
        );
        $sub = SubBidang::firstOrCreate(
            ['bidang_id' => $bidang->bidang_id, 'kode_sub_bidang' => 'SUB-INFRA'],
            ['nama_sub_bidang' => 'Sub Bidang Infrastruktur'],
        );

        $make = function (string $username, string $role, array $org = []): User {
            $user = User::updateOrCreate(['username' => $username], [
                'name'      => ucwords(str_replace('_', ' ', $username)),
                'email'     => "{$username}@simris.local",
                'password'  => 'password',
                'is_active' => true,
                ...$org,
            ]);
            $user->syncRoles([$role]);

            return $user;
        };

        $admin    = $make('admin_satker', 'admin_satker', ['satker_id' => $satker->satker_id]);
        $kasatker = $make('kasatker', 'kasatker', ['satker_id' => $satker->satker_id]);
        $kabid    = $make('kabid', 'kabid', ['bidang_id' => $bidang->bidang_id]);
        $kasubbid = $make('kasubbid', 'kasubbid', ['sub_bidang_id' => $sub->sub_bidang_id]);
        $make('staf', 'staf', ['sub_bidang_id' => $sub->sub_bidang_id]);

        // Kepala unit (dipakai untuk menentukan approver di Step 5)
        $satker->update(['kepala_user_id' => $kasatker->users_id]);
        $bidang->update(['kepala_user_id' => $kabid->users_id]);
        $sub->update(['kepala_user_id' => $kasubbid->users_id]);

        foreach ([
            'KAT-HW'   => 'Perangkat Keras',
            'KAT-SW'   => 'Perangkat Lunak',
            'KAT-DATA' => 'Data & Informasi',
            'KAT-NET'  => 'Jaringan',
        ] as $kode => $nama) {
            AssetCategory::firstOrCreate(['kode_kategori' => $kode], ['nama_kategori' => $nama]);
        }
    }
}