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


Breadcrumbs::for('admin.keuangan', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Keuangan');
});

Breadcrumbs::for('admin.keuangan.stok-obat-ruangan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Stok Obat Ruangan', route('admin.keuangan.stok-obat-ruangan'));
});

Breadcrumbs::for('admin.keuangan.rekap-piutang-pasien', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Rekap Piutang Pasien', route('admin.keuangan.rekap-piutang-pasien'));
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

Breadcrumbs::for('admin.keuangan.riwayat-jurnal-perbaikan', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Riwayat Jurnal Perbaikan', route('admin.keuangan.riwayat-jurnal-perbaikan'));
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

Breadcrumbs::for('admin.keuangan.pelaporan-rkat', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('pelaporan RKAT', route('admin.keuangan.pelaporan-rkat'));
});

Breadcrumbs::for('admin.keuangan.pemantauan-rkat', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('Pemantauan RKAT', route('admin.keuangan.pemantauan-rkat'));
});

Breadcrumbs::for('admin.keuangan.kategori-rkat', function (Trail $trail): void {
    $trail->parent('admin.keuangan');
    $trail->push('kategori RKAT', route('admin.keuangan.kategori-rkat'));
});


Breadcrumbs::for('admin.farmasi', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Farmasi');
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
    $trail->push('Kunjungan per Bentuk Obat', route('admin.farmasi.kunjungan-per-bentuk-obat'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-per-poli', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan per Poli', route('admin.farmasi.kunjungan-per-poli'));
});

Breadcrumbs::for('admin.farmasi.perbandingan-po-obat', function (Trail $trail): void {
    $trail->parent('admin.farmasi');
    $trail->push('Perbandingan PO Obat', route('admin.farmasi.perbandingan-po-obat'));
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


Breadcrumbs::for('admin.route-list', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Route List', route('admin.route-list'));
});

Breadcrumbs::for('admin.log-viewer', function (Trail $trail): void {
    $trail->parent('admin.dashboard');
    $trail->push('Log Viewer', route('admin.log-viewer'));
});