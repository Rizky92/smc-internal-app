<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;


Breadcrumbs::for('admin.dashboard', function (Trail $trail) {
    $trail->push('Dashboard', route('admin.dashboard'));
});


Breadcrumbs::for('admin.perawatan', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Perawatan');
});

Breadcrumbs::for('admin.perawatan.daftar-pasien-ranap', function (Trail $trail) {
    $trail->parent('admin.perawatan');
    $trail->push('Daftar Pasien Ranap', route('admin.perawatan.daftar-pasien-ranap'));
});

Breadcrumbs::for('admin.perawatan.laporan-pasien-ranap', function (Trail $trail) {
    $trail->parent('admin.perawatan');
    $trail->push('Laporan Pasien Ranap', route('admin.perawatan.laporan-pasien-ranap'));
});


Breadcrumbs::for('admin.keuangan', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Keuangan');
});

Breadcrumbs::for('admin.keuangan.stok-obat-ruangan', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Stok Obat Ruangan', route('admin.keuangan.stok-obat-ruangan'));
});

Breadcrumbs::for('admin.keuangan.piutang-pasien', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Piutang Pasien', route('admin.keuangan.piutang-pasien'));
});

Breadcrumbs::for('admin.keuangan.laporan-tambahan-biaya', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tambahan Biaya', route('admin.keuangan.laporan-tambahan-biaya'));
});

Breadcrumbs::for('admin.keuangan.laporan-potongan-biaya', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Potongan Biaya', route('admin.keuangan.laporan-potongan-biaya'));
});

Breadcrumbs::for('admin.keuangan.laporan-selesai-billing', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Selesai Billing', route('admin.keuangan.laporan-selesai-billing'));
});

Breadcrumbs::for('admin.keuangan.jurnal-po-supplier', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Jurnal PO Supplier', route('admin.keuangan.jurnal-po-supplier'));
});

Breadcrumbs::for('admin.keuangan.jurnal-piutang-lunas', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Jurnal Piutang Lunas', route('admin.keuangan.jurnal-piutang-lunas'));
});

Breadcrumbs::for('admin.keuangan.buku-besar', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Buku Besar', route('admin.keuangan.buku-besar'));
});

Breadcrumbs::for('admin.keuangan.laba-rugi-rekening', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laba Rugi Rekening', route('admin.keuangan.laba-rugi-rekening'));
});

Breadcrumbs::for('admin.keuangan.dpjp-piutang-ranap', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('DPJP Piutang Ranap', route('admin.keuangan.dpjp-piutang-ranap'));
});

Breadcrumbs::for('admin.keuangan.perbaikan-tgl-jurnal', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('DPJP Piutang Ranap', route('admin.keuangan.perbaikan-tgl-jurnal'));
});

Breadcrumbs::for('admin.keuangan.laporan-tindakan-lab', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tindakan Lab', route('admin.keuangan.laporan-tindakan-lab'));
});

Breadcrumbs::for('admin.keuangan.laporan-tindakan-radiologi', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Laporan Tindakan Rdlg.', route('admin.keuangan.laporan-tindakan-radiologi'));
});


Breadcrumbs::for('admin.farmasi', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Farmasi');
});

Breadcrumbs::for('admin.farmasi.stok-darurat', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Stok Darurat', route('admin.farmasi.stok-darurat'));
});

Breadcrumbs::for('admin.farmasi.obat-per-dokter', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Obat Per Dokter', route('admin.farmasi.obat-per-dokter'));
});

Breadcrumbs::for('admin.farmasi.laporan-produksi', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Laporan Produksi', route('admin.farmasi.laporan-produksi'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-per-bentuk-obat', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan per Bentuk Obat', route('admin.farmasi.kunjungan-per-bentuk-obat'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-per-poli', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan per Poli', route('admin.farmasi.kunjungan-per-poli'));
});

Breadcrumbs::for('admin.farmasi.perbandingan-po-obat', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Perbandingan PO Obat', route('admin.farmasi.perbandingan-po-obat'));
});


Breadcrumbs::for('admin.rekam-medis', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Rekam Medis');
});

Breadcrumbs::for('admin.rekam-medis.laporan-statistik', function (Trail $trail) {
    $trail->parent('admin.rekam-medis');
    $trail->push('Laporan Statistik', route('admin.rekam-medis.laporan-statistik'));
});

Breadcrumbs::for('admin.rekam-medis.laporan-demografi', function (Trail $trail) {
    $trail->parent('admin.rekam-medis');
    $trail->push('Demografi Pasien', route('admin.rekam-medis.laporan-demografi'));
});


Breadcrumbs::for('admin.logistik', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Logistik');
});

Breadcrumbs::for('admin.logistik.input-minmax-stok', function (Trail $trail) {
    $trail->parent('admin.logistik');
    $trail->push('Input Minmax Stok', route('admin.logistik.input-minmax-stok'));
});

Breadcrumbs::for('admin.logistik.stok-darurat', function (Trail $trail) {
    $trail->parent('admin.logistik');
    $trail->push('Stok Darurat', route('admin.logistik.stok-darurat'));
});


Breadcrumbs::for('admin.manajemen-user', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Manajemen User', route('admin.manajemen-user'));
});


Breadcrumbs::for('admin.hak-akses', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Hak Akses');
});

Breadcrumbs::for('admin.hak-akses.siap', function (Trail $trail) {
    $trail->parent('admin.hak-akses');
    $trail->push('SMC Internal App', route('admin.hak-akses.siap'));
});

Breadcrumbs::for('admin.hak-akses.khanza', function (Trail $trail) {
    $trail->parent('admin.hak-akses');
    $trail->push('SIMRS Khanza', route('admin.hak-akses.khanza'));
});


Breadcrumbs::for('admin.route-list', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Route List', route('admin.route-list'));
});

Breadcrumbs::for('admin.log-viewer', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Log Viewer', route('admin.log-viewer'));
});