<?php

namespace Database\Seeders;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

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

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();

        Permission::create(['name' => 'farmasi.kunjungan-per-bentuk-obat.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.kunjungan-per-poli.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.input-minmax-stok.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.laporan-produksi.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.obat-per-dokter.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.perbandingan-po-obat.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'farmasi.stok-darurat.read', 'guard_name' => 'web']);

        Permission::create(['name' => 'keuangan.account-payable.read-medis', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.account-payable.read-nonmedis', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.account-receivable.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.buku-besar.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.dpjp-piutang-ranap.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.ubah-tanggal', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.jurnal-piutang-lunas.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.jurnal-po-supplier.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laba-rugi-rekening.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laporan-potongan-biaya.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laporan-selesai-billing.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laporan-tambahan-pasien.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-lab.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-radiologi.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.rekap-piutang-aging.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.riwayat-jurnal-perbaikan.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'keuangan.stok-obat-ruangan.read', 'guard_name' => 'web']);

        Permission::create(['name' => 'logistik.input-minmax-stok.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'logistik.input-minmax-stok.delete', 'guard_name' => 'web']);
        Permission::create(['name' => 'logistik.input-minmax-stok.update', 'guard_name' => 'web']);
        Permission::create(['name' => 'logistik.input-minmax-stok.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'logistik.stok-darurat.read', 'guard_name' => 'web']);

        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.update-harga-kamar', 'guard_name' => 'web']);
        Permission::create(['name' => 'perawatan.laporan-pasien-ranap.read', 'guard_name' => 'web']);

        Permission::create(['name' => 'rekam-medis.laporan-demografi.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'rekam-medis.laporan-statistik.read', 'guard_name' => 'web']);
        Permission::create(['name' => 'rekam-medis.status-data-pasien.read', 'guard_name' => 'web']);

        $keuanganPermissions = [
            'keuangan.account-payable.read-medis',
            'keuangan.account-payable.read-nonmedis',
            'keuangan.account-receivable.read',
            'keuangan.buku-besar.read',
            'keuangan.dpjp-piutang-ranap.read',
            'keuangan.jurnal-perbaikan.read',
            'keuangan.jurnal-perbaikan.ubah-tanggal',
            'keuangan.jurnal-piutang-lunas.read',
            'keuangan.jurnal-po-supplier.read',
            'keuangan.laba-rugi-rekening.read',
            'keuangan.laporan-potongan-biaya.read',
            'keuangan.laporan-selesai-billing.read',
            'keuangan.laporan-tambahan-pasien.read',
            'keuangan.laporan-tindakan-lab.read',
            'keuangan.laporan-tindakan-radiologi.read',
            'keuangan.rekap-piutang-pasien.read',
            'keuangan.riwayat-jurnal-perbaikan.read',
            'keuangan.stok-obat-ruangan.read',
        ];

        $farmasiPermissions = [
            'faramsi.kunjungan-per-bentuk-obat.read',
            'faramsi.kunjungan-per-poli.read',
            'faramsi.input-minmax-stok.read',
            'faramsi.laporan-produksi.read',
            'faramsi.obat-per-dokter.read',
            'faramsi.perbandingan-po-obat.read',
            'faramsi.stok-darurat.read',
            'keuangan.stok-obat-ruangan.read',
        ];

        $perawatanPermissions = [
            'perawatan.daftar-pasien-ranap.read',
            'perawatan.laporan-pasien-ranap.read',
        ];

        $rekamMedisPermissions = [
            'rekam-medis.laporan-demografi.read',
            'rekam-medis.laporan-statistik.read',
            'rekam-medis.status-data-pasien.read',
        ];

        $logistikPermissions = [
            'logistik.input-minmax-stok.read',
            'logistik.stok-darurat.read',
        ];

        $kasirPermissions = [
            'perawatan.daftar-pasien-ranap.read',
            'perawatan.daftar-pasien-ranap.update-harga-kamar',
        ];

        $MODPermissions = [
            'perawatan.daftar-pasien-ranap.read',
            'perawatan.laporan-pasien-ranap.read',
        ];

        // Superadmin role name, bypasses all permissions
        $superadminRole = Role::create(['name' => config('permission.superadmin_name'), 'guard_name' => 'web']);
        
        $perawatanRole = Role::create(['name' => 'Perawatan', 'guard_name' => 'web']);
        $keuanganRole = Role::create(['name' => 'Keuangan', 'guard_name' => 'web']);
        $farmasiRole = Role::create(['name' => 'Farmasi', 'guard_name' => 'web']);
        $rekamMedisRole = Role::create(['name' => 'Rekam Medis', 'guard_name' => 'web']);
        $logistikRole = Role::create(['name' => 'Logistik', 'guard_name' => 'web']);
        $kasirRole = Role::create(['name' => 'Kasir', 'guard_name' => 'web']);
        $MODRole = Role::create(['name' => 'MOD', 'guard_name' => 'web']);

        $keuanganRole->givePermissionTo($keuanganPermissions);
        $perawatanRole->givePermissionTo($perawatanPermissions);
        $farmasiRole->givePermissionTo($farmasiPermissions);
        $rekamMedisRole->givePermissionTo($rekamMedisPermissions);
        $logistikRole->givePermissionTo($logistikPermissions);
        $kasirRole->givePermissionTo($kasirPermissions);
        $MODRole->givePermissionTo($MODPermissions);
        
        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
        DB::setDefaultConnection('mysql_sik');

        $user = User::findByNRP('88888888');

        $user->assignRole($superadminRole);
    }
}
