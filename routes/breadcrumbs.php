<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

Breadcrumbs::for('admin.dashboard', function (Trail $trail) {
    $trail->push('Dashboard', route('admin.dashboard'));
});

Breadcrumbs::for('admin.rawat-inap', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Daftar Pasien Ranap', route('admin.rawat-inap'));
});

Breadcrumbs::for('admin.farmasi', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Farmasi');
});

Breadcrumbs::for('admin.farmasi.darurat-stok', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Darurat Stok', route('admin.farmasi.darurat-stok'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-pasien-per-poli', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan per Poli', route('admin.farmasi.kunjungan-pasien-per-poli'));
});

Breadcrumbs::for('admin.farmasi.kunjungan-resep', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Kunjungan per Bentuk Obat', route('admin.farmasi.kunjungan-resep'));
});

Breadcrumbs::for('admin.farmasi.laporan-tahunan', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Laporan Produksi', route('admin.farmasi.laporan-tahunan'));
});

Breadcrumbs::for('admin.farmasi.obat-perdokter', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Obat Per Dokter', route('admin.farmasi.obat-perdokter'));
});

Breadcrumbs::for('admin.farmasi.perbandingan-po-obat', function (Trail $trail) {
    $trail->parent('admin.farmasi');
    $trail->push('Perbandingan PO Obat', route('admin.farmasi.perbandingan-po-obat'));
});

Breadcrumbs::for('admin.keuangan', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Keuangan');
});

Breadcrumbs::for('admin.keuangan.stok-obat-per-ruangan', function (Trail $trail) {
    $trail->parent('admin.keuangan');
    $trail->push('Stok Obat Ruangan', route('admin.keuangan.stok-obat-per-ruangan'));
});

Breadcrumbs::for('admin.logistik', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Logistik');
});

Breadcrumbs::for('admin.logistik.darurat-stok', function (Trail $trail) {
    $trail->parent('admin.logistik');
    $trail->push('Darurat Stok', route('admin.logistik.darurat-stok'));
});

Breadcrumbs::for('admin.logistik.minmax', function (Trail $trail) {
    $trail->parent('admin.logistik');
    $trail->push('Input Stok Minmax', route('admin.logistik.minmax'));
});

Breadcrumbs::for('admin.rekam-medis', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Rekam Medis');
});

Breadcrumbs::for('admin.rekam-medis.demografi-pasien', function (Trail $trail) {
    $trail->parent('admin.rekam-medis');
    $trail->push('Demografi Pasien', route('admin.rekam-medis.demografi-pasien'));
});

Breadcrumbs::for('admin.rekam-medis.laporan-statistik', function (Trail $trail) {
    $trail->parent('admin.rekam-medis');
    $trail->push('Laporan Statistik', route('admin.rekam-medis.laporan-statistik'));
});

Breadcrumbs::for('admin.users', function (Trail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Admin');
});

Breadcrumbs::for('admin.users.hak-akses', function (Trail $trail) {
    $trail->parent('admin.users');
    $trail->push('Pengaturan Hak Akses', route('admin.users.hak-akses'));
});

Breadcrumbs::for('admin.users.manajemen', function (Trail $trail) {
    $trail->parent('admin.users');
    $trail->push('Manajemen User', route('admin.users.manajemen'));
});

Breadcrumbs::for('admin.users.hak-akses-user', function (Trail $trail) {
    $trail->parent('admin.users');
    $trail->push('Khanza Set Akses User', route('admin.users.hak-akses-user'));
});