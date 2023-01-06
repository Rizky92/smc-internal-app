<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Farmasi\KunjunganFarmasiPasienPerPoli;
use App\Http\Livewire\Farmasi\KunjunganResepPasien;
use App\Http\Livewire\Farmasi\LaporanProduksiTahunan;
use App\Http\Livewire\Farmasi\PenggunaanObatPerdokter;
use App\Http\Livewire\Farmasi\RingkasanPerbandinganBarangPO;
use App\Http\Livewire\Farmasi\StokDaruratFarmasi;
use App\Http\Livewire\Logistik\MinmaxBarang;
use App\Http\Livewire\Logistik\StokDaruratLogistik;
use App\Http\Livewire\Perawatan\DaftarPasienRanap;
use App\Http\Livewire\RekamMedis\LaporanDemografiPasien;
use App\Http\Livewire\RekamMedis\LaporanStatistikRekamMedis;
use App\Http\Livewire\Keuangan\StokPerRuangan;
use App\Http\Livewire\Khanza\User\ManajemenUser as KhanzaManajemenUser;
use App\Http\Livewire\User\ManajemenHakAkses;
use App\Http\Livewire\User\ManajemenUser;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', HomeController::class);

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);

Route::middleware('auth')
    ->group(function () {
        Route::post('logout', LogoutController::class)->name('logout');
    });

Route::prefix('admin')
    ->as('admin.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('rawat-inap', DaftarPasienRanap::class)
            ->middleware([
                'can:perawatan.rawat-inap.read',
                'can:perawatan.rawat-inap.batal-ranap',
            ])
            ->name('rawat-inap');

        Route::prefix('keuangan')
            ->as('keuangan.')
            ->group(function () {
                Route::get('stok-obat-per-ruangan', StokPerRuangan::class)
                    ->middleware('can:manajemen.stok-obat-per-ruangan.read')
                    ->name('stok-obat-per-ruangan');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('darurat-stok', StokDaruratFarmasi::class)
                    ->middleware('can:farmasi.darurat-stok.read')
                    ->name('darurat-stok');

                Route::get('penggunaan-obat-perdokter', PenggunaanObatPerdokter::class)
                    ->middleware('can:farmasi.penggunaan-obat-perdokter.read')
                    ->name('obat-perdokter');

                Route::get('laporan-tahunan', LaporanProduksiTahunan::class)
                    ->middleware('can:farmasi.laporan-tahunan.read')
                    ->name('laporan-tahunan');

                Route::get('kunjungan-resep', KunjunganResepPasien::class)
                    ->middleware('can:farmasi.kunjungan-resep.read')
                    ->name('kunjungan-resep');

                Route::get('kunjungan-pasien-per-poli', KunjunganFarmasiPasienPerPoli::class)
                    ->middleware('can:farmasi.kunjungan-pasien-per-poli.read')
                    ->name('kunjungan-pasien-per-poli');

                Route::get('ringkasan-perbandingan-po-obat', RingkasanPerbandinganBarangPO::class)
                    ->middleware('can:farmasi.perbandingan-po-obat.read')
                    ->name('perbandingan-po-obat');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', LaporanStatistikRekamMedis::class)
                    ->middleware('can:rekam-medis.laporan-statistik.read')
                    ->name('laporan-statistik');

                Route::get('laporan-demografi-pasien', LaporanDemografiPasien::class)
                    ->middleware('can:rekam-medis.demografi-pasien.read')
                    ->name('demografi-pasien');
            });

        Route::prefix('logistik')
            ->as('logistik.')
            ->group(function () {
                Route::get('darurat-stok', StokDaruratLogistik::class)
                    ->middleware('can:logistik.darurat-stok.read')
                    ->name('darurat-stok');

                Route::get('minmax', MinmaxBarang::class)
                    ->middleware([
                        'can:logistik.stok-minmax.read',
                        'can:logistik.stok-minmax.update',
                    ])
                    ->name('minmax');
            });

        Route::prefix('users')
            ->as('users.')
            ->middleware('role:' . config('permission.superadmin_name'))
            ->group(function () {
                Route::get('/manajemen', ManajemenUser::class)
                    ->name('manajemen');

                Route::get('/hak-akses', ManajemenHakAkses::class)
                    ->name('hak-akses');

                Route::get('hak-akses-user', KhanzaManajemenUser::class)
                    ->name('hak-akses-user');
            });
    });
