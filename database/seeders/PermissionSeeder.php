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

        DB::connection('mysql_smc')->table('model_has_roles')->truncate();
        DB::connection('mysql_smc')->table('model_has_permissions')->truncate();
        DB::connection('mysql_smc')->table('role_has_permissions')->truncate();

        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.read']);
        Permission::create(['name' => 'perawatan.daftar-pasien-ranap.update-harga-kamar']);
        Permission::create(['name' => 'perawatan.laporan-pasien-ranap.read']);
        Permission::create(['name' => 'perawatan.laporan-transaksi-gantung.read']);
        Permission::create(['name' => 'perawatan.laporan-hasil-pemeriksaan.read']);

        Permission::create(['name' => 'keuangan.rkat-penetapan.create']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.read']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.update']);
        Permission::create(['name' => 'keuangan.rkat-penetapan.delete']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.create']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.read']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.update']);
        Permission::create(['name' => 'keuangan.rkat-pelaporan.delete']);
        Permission::create(['name' => 'keuangan.rkat-pemantauan.read']);
        Permission::create(['name' => 'keuangan.rkat-kategori.create']);
        Permission::create(['name' => 'keuangan.rkat-kategori.read']);
        Permission::create(['name' => 'keuangan.rkat-kategori.update']);
        Permission::create(['name' => 'keuangan.rkat-kategori.delete']);
        Permission::create(['name' => 'keuangan.stok-obat-ruangan.read']);
        Permission::create(['name' => 'keuangan.laporan-tambahan-biaya.read']);
        Permission::create(['name' => 'keuangan.laporan-potongan-biaya.read']);
        Permission::create(['name' => 'keuangan.laporan-selesai-billing.read']);
        Permission::create(['name' => 'keuangan.jurnal-po-supplier.read']);
        Permission::create(['name' => 'keuangan.jurnal-piutang-lunas.read']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.read']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan.ubah-tanggal']);
        Permission::create(['name' => 'keuangan.jurnal-perbaikan-riwayat.read']);
        Permission::create(['name' => 'keuangan.buku-besar.read']);
        Permission::create(['name' => 'keuangan.laba-rugi-rekening.read']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-lab.read']);
        Permission::create(['name' => 'keuangan.laporan-tindakan-radiologi.read']);
        Permission::create(['name' => 'keuangan.account-receivable.read']);
        Permission::create(['name' => 'keuangan.account-receivable.validasi-piutang']);
        Permission::create(['name' => 'keuangan.account-payable.read-medis']);
        Permission::create(['name' => 'keuangan.account-payable.read-nonmedis']);
        Permission::create(['name' => 'keuangan.laporan-trial-balance.read']);
        Permission::create(['name' => 'keuangan.posting-jurnal.create']);
        Permission::create(['name' => 'keuangan.posting-jurnal.read']);
        Permission::create(['name' => 'keuangan.laporan-faktur-pajak.read']);
        Permission::create(['name' => 'keuangan.igd-ke-rawat-inap.read']);

        Permission::create(['name' => 'farmasi.stok-darurat.read']);
        Permission::create(['name' => 'farmasi.pemakaian-stok.read']);
        Permission::create(['name' => 'farmasi.laporan-produksi.read']);
        Permission::create(['name' => 'farmasi.obat-per-dokter.read']);
        Permission::create(['name' => 'farmasi.kunjungan-per-bentuk-obat.read']);
        Permission::create(['name' => 'farmasi.kunjungan-per-poli.read']);
        Permission::create(['name' => 'farmasi.perbandingan-po-obat.read']);
        Permission::create(['name' => 'farmasi.laporan-pembuatan-soap.read']);
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-napza.read']);
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-morphine.read']);
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-tb.read']);
        Permission::create(['name' => 'farmasi.defecta-depo.read']);
        Permission::create(['name' => 'farmasi.daftar-riwayat-obat-alkes.read']);
        Permission::create(['name' => 'farmasi.rincian-perbandingan-po.read']);
        Permission::create(['name' => 'farmasi.rincian-kunjungan-ralan.read']);

        Permission::create(['name' => 'rekam-medis.laporan-statistik.read']);
        Permission::create(['name' => 'rekam-medis.laporan-demografi.read']);
        Permission::create(['name' => 'rekam-medis.status-data-pasien.read']);

        Permission::create(['name' => 'logistik.input-minmax-stok.create']);
        Permission::create(['name' => 'logistik.input-minmax-stok.read']);
        Permission::create(['name' => 'logistik.input-minmax-stok.update']);
        Permission::create(['name' => 'logistik.input-minmax-stok.delete']);
        Permission::create(['name' => 'logistik.stok-darurat.read']);

        Permission::create(['name' => 'aplikasi.bidang-unit.create']);
        Permission::create(['name' => 'aplikasi.bidang-unit.read']);
        Permission::create(['name' => 'aplikasi.bidang-unit.update']);
        Permission::create(['name' => 'aplikasi.bidang-unit.delete']);

        Permission::create(['name' => 'aplikasi.pengaturan-rkat.read']);
        Permission::create(['name' => 'aplikasi.pengaturan-rkat.update']);

        Permission::create(['name' => 'antrean.manajemen-pintu.create']);
        Permission::create(['name' => 'antrean.manajemen-pintu.read']);
        Permission::create(['name' => 'antrean.manajemen-pintu.update']);
        Permission::create(['name' => 'antrean.manajemen-pintu.delete']);

        // Superadmin role name, bypasses all permissions
        $superadminRole = Role::create(['name' => config('permission.superadmin_name')]);

        /** @var User */
        $user = User::findByNRP('221203');
        $user->assignRole($superadminRole);

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
}
