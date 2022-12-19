<?php

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $farmasiPermissions = [
            Permission::create(['name' => 'farmasi.darurat-stok.read']),
            Permission::create(['name' => 'farmasi.penggunaan-obat-perdokter.read']),
            Permission::create(['name' => 'farmasi.laporan-tahunan.read']),
        ];

        $rekamMedisPermissions = [
            Permission::create(['name' => 'rekam-medis.laporan-statistik.read']),
        ];

        $logistikPermissions = [
            Permission::create(['name' => 'logistik.stok-minmax.create']),
            Permission::create(['name' => 'logistik.stok-minmax.read']),
            Permission::create(['name' => 'logistik.stok-minmax.update']),
            Permission::create(['name' => 'logistik.stok-minmax.delete']),

            Permission::create(['name' => 'logistik.darurat-stok.read']),
        ];

        $developRole = Role::create(['name' => 'develop', 'guard_name' => 'web']);
        $farmasiRole = Role::create(['name' => 'farmasi', 'guard_name' => 'web']);
        $rekamMedisRole = Role::create(['name' => 'rekam-medis', 'guard_name' => 'web']);
        $logistikRole = Role::create(['name' => 'logistik', 'guard_name' => 'web']);

        $farmasiRole->givePermissionTo($farmasiPermissions);
        $rekamMedisRole->givePermissionTo($rekamMedisPermissions);
        $logistikRole->givePermissionTo($logistikPermissions);
    }
}
