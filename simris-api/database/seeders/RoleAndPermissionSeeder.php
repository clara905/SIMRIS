<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // master & organisasi (Super Admin)
            'master.manage',
            'satker.manage',
            // user & privilege (Admin Satker di satkernya)
            'user.view',
            'user.manage',
            // SOP (Admin Satker)
            'sop.manage',
            // aset (Kasubbid kelola; lainnya lihat)
            'asset.view',
            'asset.manage',
            // penilaian risiko (Staf)
            'risk.view',
            'risk.assess',
            'risk.approve',
            // mitigasi
            'mitigation.view',
            'mitigation.manage',
            'mitigation.verify',
            // laporan & monitoring
            'report.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $roles = [
            // Super Admin: semua privilege, lintas satker
            'super_admin' => $permissions,

            // Admin Satker: kelola user/privilege & SOP di satker sendiri
            'admin_satker' => [
                'user.view', 'user.manage', 'sop.manage',
                'asset.view', 'risk.view', 'mitigation.view', 'report.view',
            ],

            // Kasatker: monitoring seluruh bidang + approval final
            'kasatker' => [
                'asset.view', 'risk.view', 'risk.approve',
                'mitigation.view', 'report.view',
            ],

            // Kabid: monitoring bidangnya + review laporan bidang
            'kabid' => [
                'asset.view', 'risk.view', 'risk.approve',
                'mitigation.view', 'report.view',
            ],

            // Kasubbid: kelola aset + review laporan staf + verifikasi mitigasi
            'kasubbid' => [
                'asset.view', 'asset.manage',
                'risk.view', 'risk.approve',
                'mitigation.view', 'mitigation.verify', 'report.view',
            ],

            // Staf: menilai aset, identifikasi ancaman/kerentanan, input mitigasi
            'staf' => [
                'asset.view',
                'risk.view', 'risk.assess',
                'mitigation.view', 'mitigation.manage',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($perms);
        }
    }
}
