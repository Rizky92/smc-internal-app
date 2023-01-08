<?php

namespace Database\Seeders;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();
        DB::setDefaultConnection('mysql_smc');

        Permission::truncate();
        Role::truncate();

        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();

        $perawatanPermissions = [
            Permission::create(['name' => 'perawatan.daftar-pasien-ranap.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'perawatan.rawat-inap.batal-ranap', 'guard_name' => 'web']),
        ];

        $keuanganPermissions = [
            Permission::create(['name' => 'keuangan.stok-obat-ruangan.read', 'guard_name' => 'web']),
        ];

        $farmasiPermissions = [
            Permission::create(['name' => 'farmasi.stok-darurat.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'farmasi.obat-per-dokter.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'farmasi.kunjungan-per-bentuk-obat.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'farmasi.kunjungan-per-poli.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'farmasi.laporan-produksi-tahunan.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'farmasi.perbandingan-po-obat.read', 'guard_name' => 'web']),
        ];

        $rekamMedisPermissions = [
            Permission::create(['name' => 'rekam-medis.laporan-statistik.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'rekam-medis.laporan-demografi.read', 'guard_name' => 'web']),
        ];

        $logistikPermissions = [
            Permission::create(['name' => 'logistik.input-minmax-stok.input', 'guard_name' => 'web']),
            Permission::create(['name' => 'logistik.input-minmax-stok.read', 'guard_name' => 'web']),
            Permission::create(['name' => 'logistik.stok-darurat.read', 'guard_name' => 'web']),
        ];

        // Superadmin role name, bypasses all permissions
        Role::create(['name' => config('permission.superadmin_name'), 'guard_name' => 'web']);
        
        $perawatanRole = Role::create(['name' => 'Perawatan', 'guard_name' => 'web']);
        $keuanganRole = Role::create(['name' => 'Keuangan', 'guard_name' => 'web']);
        $farmasiRole = Role::create(['name' => 'Farmasi', 'guard_name' => 'web']);
        $rekamMedisRole = Role::create(['name' => 'Rekam Medis', 'guard_name' => 'web']);
        $logistikRole = Role::create(['name' => 'Logistik', 'guard_name' => 'web']);

        $keuanganRole->givePermissionTo($keuanganPermissions);
        $perawatanRole->givePermissionTo($perawatanPermissions);
        $farmasiRole->givePermissionTo($farmasiPermissions);
        $rekamMedisRole->givePermissionTo($rekamMedisPermissions);
        $logistikRole->givePermissionTo($logistikPermissions);

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
        DB::setDefaultConnection('mysql_sik');
    }
}
