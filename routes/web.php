<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Farmasi\KunjunganResepPasien;
use App\Http\Livewire\Farmasi\LaporanProduksiTahunan;
use App\Http\Livewire\Farmasi\PenggunaanObatPerdokter;
use App\Http\Livewire\Farmasi\StokDaruratFarmasi;
use App\Http\Livewire\Logistik\StokDaruratLogistik;
use App\Http\Livewire\Logistik\StokInputMinmaxBarang;
use App\Http\Livewire\Perawatan\DaftarPasienRanap;
use App\Http\Livewire\RekamMedis\LaporanStatistikRekamMedis;
use App\Http\Livewire\User\ManajemenUser;
use App\Http\Livewire\User\SetHakAkses;
use Illuminate\Support\Facades\Route;
use Livewire\Controllers\HttpConnectionHandler;

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

// Cari tau alternative laravel untuk kembali ke dashboard dengan url yang memiliki 1 segmen
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

        Route::prefix('perawatan')
            ->as('perawatan.')
            ->group(function () {
                Route::get('pasien-ranap', DaftarPasienRanap::class)
                    ->middleware([
                        'can:perawatan.pasien-ranap.read',
                        'can:perawatan.pasien-ranap.pulangkan-pasien',
                    ])
                    ->name('pasien-ranap');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('darurat-stok', StokDaruratFarmasi::class)
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
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', LaporanStatistikRekamMedis::class)
                    ->middleware('can:rekam-medis.laporan-statistik.read')
                    ->name('laporan-statistik');
            });

        Route::prefix('logistik')
            ->as('logistik.')
            ->group(function () {
                Route::get('darurat-stok', StokDaruratLogistik::class)
                    ->middleware('can:logistik.darurat-stok.read')
                    ->name('darurat-stok');

                Route::get('minmax', StokInputMinmaxBarang::class)
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

                Route::get('/hak-akses', SetHakAkses::class)
                    ->name('hak-akses');
            });
    });
