<?php

namespace Database\Seeders;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();

        Permission::truncate();
        Role::truncate();

        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();

        Permission::create(['name' => 'farmasi.kunjungan-per-bentuk-obat.read']);
        Permission::create(['name' => 'farmasi.kunjungan-per-poli.read']);
        Permission::create(['name' => 'farmasi.laporan-produksi.read']);
        Permission::create(['name' => 'farmasi.obat-per-dokter.read']);
        Permission::create(['name' => 'farmasi.perbandingan-po-obat.read']);
        Permission::create(['name' => 'farmasi.stok-darurat.read']);
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-napza.read']);
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-morphine.read']);

        Permission::create(['name' => 'keuangan.account-payable.read-medis']);
        Permission::create(['name' => 'keuangan.account-payable.read-nonmedis']);
        Permission::create(['name' => 'keuangan.account-receivable.read']);
        Permission::create(['name' => 'keuangan.buku-besar.read']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan-riwayat.read']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.read']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.ubah-tanggal']);
        Permission::create(['name' => 'keuangan.jurnal-piutang-lunas.read']);
        Permission::create(['name' => 'keuangan.jurnal-po-supplier.read']);
        Permission::create(['name' => 'keuangan.laba-rugi-rekening.read']);
        Permission::create(['name' => 'keuangan.laporan-potongan-biaya.read']);
        Permission::create(['name' => 'keuangan.laporan-selesai-billing.read']);
        Permission::create(['name' => 'keuangan.laporan-tambahan-biaya.read']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-lab.read']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-radiologi.read']);
        Permission::create(['name' => 'keuangan.rkat-kategori.create']);
        Permission::create(['name' => 'keuangan.rkat-kategori.delete']);
        Permission::create(['name' => 'keuangan.rkat-kategori.read']);
        Permission::create(['name' => 'keuangan.rkat-kategori.update']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.create']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.delete']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.read']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.update']);
        Permission::create(['name' => 'keuangan.rkat-pemantauan.read']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.create']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.delete']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.read']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.update']);
        Permission::create(['name' => 'keuangan.stok-obat-ruangan.read']);

        Permission::create(['name' => 'logistik.input-minmax-stok.create']);
        Permission::create(['name' => 'logistik.input-minmax-stok.delete']);
        Permission::create(['name' => 'logistik.input-minmax-stok.read']);
        Permission::create(['name' => 'logistik.input-minmax-stok.update']);
        Permission::create(['name' => 'logistik.stok-darurat.read']);

        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.read']);
        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.update-harga-kamar']);
        Permission::create(['name' => 'perawatan.laporan-pasien-ranap.read']);
        Permission::create(['name' => 'perawatan.laporan-transaksi-gantung.read']);

        Permission::create(['name' => 'rekam-medis.laporan-demografi.read']);
        Permission::create(['name' => 'rekam-medis.laporan-statistik.read']);
        Permission::create(['name' => 'rekam-medis.status-data-pasien.read']);

        $keuanganPermissions = [
            'keuangan.account-payable.read-medis',
            'keuangan.account-payable.read-nonmedis',
            'keuangan.account-receivable.read',
            'keuangan.buku-besar.read',
            'keuangan.jurnal-perbaikan-riwayat.read',
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
            'keuangan.stok-obat-ruangan.read',
            'perawatan.laporan-transaksi-gantung.read',
        ];

        $RKATPermissions = [
            'keuangan.rkat-kategori.create',
            'keuangan.rkat-kategori.read',
            'keuangan.rkat-kategori.update',
            'keuangan.rkat-pelaporan.create',
            'keuangan.rkat-pelaporan.read',
            'keuangan.rkat-pemantauan.read',
            'keuangan.rkat-penetapan.create',
            'keuangan.rkat-penetapan.read',
        ];

        $farmasiPermissions = [
            'farmasi.kunjungan-per-bentuk-obat.read',
            'farmasi.kunjungan-per-poli.read',
            'farmasi.laporan-produksi.read',
            'farmasi.obat-per-dokter.read',
            'farmasi.perbandingan-po-obat.read',
            'farmasi.stok-darurat.read',
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
        $superadminRole = Role::create(['name' => config('permission.superadmin_name')]);
        
        $perawatanRole  = Role::create(['name' => 'Perawatan']);
        $keuanganRole   = Role::create(['name' => 'Keuangan']);
        $farmasiRole    = Role::create(['name' => 'Farmasi']);
        $rekamMedisRole = Role::create(['name' => 'Rekam Medis']);
        $logistikRole   = Role::create(['name' => 'Logistik']);
        $kasirRole      = Role::create(['name' => 'Kasir']);
        $MODRole        = Role::create(['name' => 'MOD']);

        $keuanganRole->givePermissionTo($keuanganPermissions);
        $perawatanRole->givePermissionTo($perawatanPermissions);
        $farmasiRole->givePermissionTo($farmasiPermissions);
        $rekamMedisRole->givePermissionTo($rekamMedisPermissions);
        $logistikRole->givePermissionTo($logistikPermissions);
        $kasirRole->givePermissionTo($kasirPermissions);
        $MODRole->givePermissionTo($MODPermissions);
        
        /** @var \App\Models\Aplikasi\User */
        $user = User::findByNRP('221203');

        $user->assignRole($superadminRole);

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
}
