<?php

use App\Permission;
use App\Role;
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

        $farmasiRole = Role::findByName('farmasi');
        $rekamMedisRole = Role::findByName('rekam-medis');
        $logistikRole = Role::findByName('logistik');

        $farmasiRole->givePermissionTo($farmasiPermissions);
        $rekamMedisRole->givePermissionTo($rekamMedisPermissions);
        $logistikRole->givePermissionTo($logistikPermissions);
    }
}
