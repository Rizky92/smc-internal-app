<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

Breadcrumbs::for('admin.dashboard', function (Trail $trail): void {
    $trail->push('Dashboard', route('admin.dashboard'));
});

Breadcrumbs::for('admin.perawatan', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Perawatan');
});

Breadcrumbs::for('admin.perawatan.daftar-pasien-ranap', function (Trail $trail): void {
    $trail->parent('admin.perawatan');
    $trail->push('Daftar Pasien Ranap', route('admin.perawatan.daftar-pasien-ranap'));
});

Breadcrumbs::for('admin.perawatan.laporan-pasien-ranap', function (Trail $trail): void {
    $trail->parent('admin.perawatan');
    $trail->push('Laporan Pasien Ranap', route('admin.perawatan.laporan-pasien-ranap'));
});

Breadcrumbs::for('admin.perawatan.laporan-transaksi-gantung', function (Trail $trail): void {
    $trail->parent('admin.perawatan');
    $trail->push('Transaksi Gantung', route('admin.perawatan.laporan-transaksi-gantung'));
});

Breadcrumbs::for('admin.perawatan.laporan-hasil-pemeriksaan', function (Trail $trail): void {
    $trail->parent('admin.perawatan');
    $trail->push('Laporan Hasil Pemeriksaan', route('admin.perawatan.laporan-hasil-pemeriksaan'));
});

Breadcrumbs::for('admin.lab', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Laboratorium');
});

Breadcrumbs::for('admin.lab.hasil-mcu-karyawan', function (Trail $trail): void {
    $trail->parent('admin.lab');
    $trail->push('Hasil MCU Karyawan', route('admin.lab.hasil-mcu-karyawan'));
});

Breadcrumbs::for('admin.keuangan', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Keuangan');
});

Breadcrumbs::for('admin.keuangan.stok-obat-ruangan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Stok Obat Ruangan', route('admin.keuangan.stok-obat-ruangan'));
});

Breadcrumbs::for('admin.keuangan.laporan-tambahan-biaya', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tambahan Biaya', route('admin.keuangan.laporan-tambahan-biaya'));
});

Breadcrumbs::for('admin.keuangan.laporan-potongan-biaya', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Potongan Biaya', route('admin.keuangan.laporan-potongan-biaya'));
});

Breadcrumbs::for('admin.keuangan.laporan-selesai-billing', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Selesai Billing', route('admin.keuangan.laporan-selesai-billing'));
});

Breadcrumbs::for('admin.keuangan.jurnal-supplier-po', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Jurnal Supplier PO', route('admin.keuangan.jurnal-supplier-po'));
});

Breadcrumbs::for('admin.keuangan.jurnal-piutang-lunas', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Jurnal Piutang Lunas', route('admin.keuangan.jurnal-piutang-lunas'));
});

Breadcrumbs::for('admin.keuangan.jurnal-perbaikan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Jurnal Perbaikan', route('admin.keuangan.jurnal-perbaikan'));
});

Breadcrumbs::for('admin.keuangan.jurnal-perbaikan-riwayat', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Riwayat Jurnal', route('admin.keuangan.jurnal-perbaikan-riwayat'));
});

Breadcrumbs::for('admin.keuangan.buku-besar', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Buku Besar', route('admin.keuangan.buku-besar'));
});

Breadcrumbs::for('admin.keuangan.laba-rugi-rekening', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laba Rugi Rekening', route('admin.keuangan.laba-rugi-rekening'));
});

Breadcrumbs::for('admin.keuangan.laporan-tindakan-lab', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tindakan Lab', route('admin.keuangan.laporan-tindakan-lab'));
});

Breadcrumbs::for('admin.keuangan.laporan-tindakan-radiologi', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tindakan Rdlg.', route('admin.keuangan.laporan-tindakan-radiologi'));
});

Breadcrumbs::for('admin.keuangan.account-receivable', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Piutang Aging AR', route('admin.keuangan.account-receivable'));
});

Breadcrumbs::for('admin.keuangan.account-payable', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Hutang Aging AP', route('admin.keuangan.account-payable'));
});

Breadcrumbs::for('admin.keuangan.rkat-pelaporan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('pelaporan RKAT', route('admin.keuangan.rkat-pelaporan'));
});

Breadcrumbs::for('admin.keuangan.rkat-pemantauan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Pemantauan RKAT', route('admin.keuangan.rkat-pemantauan'));
});

Breadcrumbs::for('admin.keuangan.rkat-penetapan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Penetapan RKAT', route('admin.keuangan.rkat-penetapan'));
});

Breadcrumbs::for('admin.keuangan.rkat-kategori', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('kategori RKAT', route('admin.keuangan.rkat-kategori'));
});

Breadcrumbs::for('admin.keuangan.laporan-trial-balance', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Trial Balance', route('admin.keuangan.laporan-trial-balance'));
});

Breadcrumbs::for('admin.keuangan.posting-jurnal', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Posting Jurnal', route('admin.keuangan.posting-jurnal'));
});

Breadcrumbs::for('admin.keuangan.laporan-faktur-pajak-bpjs', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Faktur Pajak BPJS', route('admin.keuangan.laporan-faktur-pajak-bpjs'));
});

Breadcrumbs::for('admin.keuangan.laporan-faktur-pajak-umum', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Faktur Pajak UMUM', route('admin.keuangan.laporan-faktur-pajak-umum'));
});

Breadcrumbs::for('admin.keuangan.laporan-faktur-pajak-asper', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Faktur Pajak AS/PER', route('admin.keuangan.laporan-faktur-pajak-asper'));
});

Breadcrumbs::for('admin.farmasi', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Farmasi');
});

Breadcrumbs::for('admin.farmasi.pemakaian-stok', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Pemakaian Stok', route('admin.farmasi.pemakaian-stok'));
});

Breadcrumbs::for('admin.farmasi.stok-darurat', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Stok Darurat', route('admin.farmasi.stok-darurat'));
});

Breadcrumbs::for('admin.farmasi.obat-per-dokter', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Obat Per Dokter', route('admin.farmasi.obat-per-dokter'));
});

Breadcrumbs::for('admin.farmasi.laporan-produksi', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Laporan Produksi', route('admin.farmasi.laporan-produksi'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-per-bentuk-obat', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan Bentuk Obat', route('admin.farmasi.kunjungan-per-bentuk-obat'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-per-poli', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan Poli', route('admin.farmasi.kunjungan-per-poli'));
});

Breadcrumbs::for('admin.farmasi.perbandingan-po-obat', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Perbandingan PO Obat', route('admin.farmasi.perbandingan-po-obat'));
});

Breadcrumbs::for('admin.farmasi.laporan-pembuatan-soap', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Pembuatan SOAP', route('admin.farmasi.laporan-pembuatan-soap'));
});

Breadcrumbs::for('admin.farmasi.laporan-pemakaian-obat-napza', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Pemakaian Obat NAPZA', route('admin.farmasi.laporan-pemakaian-obat-napza'));
});

Breadcrumbs::for('admin.farmasi.laporan-pemakaian-obat-morphine', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Pemakaian Obat Morfin', route('admin.farmasi.laporan-pemakaian-obat-morphine'));
});

Breadcrumbs::for('admin.farmasi.laporan-pemakaian-obat-tb', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Pemakaian Obat TB', route('admin.farmasi.laporan-pemakaian-obat-tb'));
});

Breadcrumbs::for('admin.farmasi.defecta-depo', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Defecta Depo', route('admin.farmasi.defecta-depo'));
});

Breadcrumbs::for('admin.farmasi.daftar-riwayat-obat-alkes', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Riwayat Obat Alkes', route('admin.farmasi.daftar-riwayat-obat-alkes'));
});

Breadcrumbs::for('admin.farmasi.rincian-perbandingan-po', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Rincian Perbandingan Barang PO', route('admin.farmasi.rincian-perbandingan-po'));
});

Breadcrumbs::for('admin.farmasi.rincian-kunjungan-ralan', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Rincian Kunjungan Ralan', route('admin.farmasi.rincian-kunjungan-ralan'));
});

Breadcrumbs::for('admin.rekam-medis', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Rekam Medis');
});

Breadcrumbs::for('admin.rekam-medis.laporan-statistik', function (Trail $trail): void {
    $trail->parent('admin.rekam-medis');
    $trail->push('Laporan Statistik', route('admin.rekam-medis.laporan-statistik'));
});

Breadcrumbs::for('admin.rekam-medis.laporan-demografi', function (Trail $trail): void {
    $trail->parent('admin.rekam-medis');
    $trail->push('Demografi Pasien', route('admin.rekam-medis.laporan-demografi'));
});

Breadcrumbs::for('admin.rekam-medis.status-data-pasien', function (Trail $trail): void {
    $trail->parent('admin.rekam-medis');
    $trail->push('Status Data Pasien', route('admin.rekam-medis.status-data-pasien'));
});

Breadcrumbs::for('admin.logistik', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Logistik');
});

Breadcrumbs::for('admin.logistik.input-minmax-stok', function (Trail $trail): void {
    $trail->parent('admin.logistik');
    $trail->push('Input Minmax Stok', route('admin.logistik.input-minmax-stok'));
});

Breadcrumbs::for('admin.logistik.stok-darurat', function (Trail $trail): void {
    $trail->parent('admin.logistik');
    $trail->push('Stok Darurat', route('admin.logistik.stok-darurat'));
});

Breadcrumbs::for('admin.manajemen-user', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Manajemen User', route('admin.manajemen-user'));
});

Breadcrumbs::for('admin.hak-akses', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Hak Akses');
});

Breadcrumbs::for('admin.hak-akses.siap', function (Trail $trail): void {
    $trail->parent('admin.hak-akses');
    $trail->push('SMC Internal App', route('admin.hak-akses.siap'));
});

Breadcrumbs::for('admin.hak-akses.khanza', function (Trail $trail): void {
    $trail->parent('admin.hak-akses');
    $trail->push('SIMRS Khanza', route('admin.hak-akses.khanza'));
});

Breadcrumbs::for('admin.aplikasi', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Aplikasi');
});

Breadcrumbs::for('admin.aplikasi.bidang-unit', function (Trail $trail): void {
    $trail->parent('admin.aplikasi');
    $trail->push('Bidang Unit', route('admin.aplikasi.bidang-unit'));
});

Breadcrumbs::for('admin.aplikasi.pengaturan', function (Trail $trail): void {
    $trail->parent('admin.aplikasi');
    $trail->push('Pengaturan', route('admin.aplikasi.pengaturan'));
});

Breadcrumbs::for('admin.route-list', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Route List', route('admin.route-list'));
});

Breadcrumbs::for('admin.log-viewer', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Log Viewer', route('admin.log-viewer'));
});

Breadcrumbs::for('admin.informasi', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Informasi');
});

Breadcrumbs::for('admin.informasi.informasi-kamar', function (Trail $trail): void {
    $trail->parent('admin.informasi');
    $trail->push('Informasi Kamar', route('admin.informasi.informasi-kamar'));
});

Breadcrumbs::for('admin.informasi.jadwal-dokter', function (Trail $trail): void {
    $trail->parent('admin.informasi');
    $trail->push('Jadwal Dokter', route('admin.informasi.jadwal-dokter'));
});

Breadcrumbs::for('jadwal-dokter', function (Trail $trail): void {
    $trail->push('Jadwal Dokter', route('jadwal-dokter'));
});

Breadcrumbs::for('admin.antrean', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Antrean');
});

Breadcrumbs::for('admin.antrean.manajemen-pintu', function (Trail $trail): void {
    $trail->parent('admin.antrean');
    $trail->push('Manajemen Pintu', route('admin.antrean.manajemen-pintu'));
});

Breadcrumbs::for('admin.job-cleaner', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Job Cleaner', route('admin.job-cleaner'));
});
