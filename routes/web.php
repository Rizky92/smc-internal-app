<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Khanza\Auth\LoginController;
use App\Http\Controllers\Khanza\Auth\LogoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Farmasi\LaporanDaruratStokController as DaruratStokFarmasiController;
use App\Http\Livewire\Farmasi\LaporanProduksiTahunan;
use App\Http\Livewire\Farmasi\PenggunaanObatPerdokter;
use App\Http\Livewire\Logistik\StokDarurat as StokDaruratLogistik;
use App\Http\Livewire\Logistik\StokInputMinmaxBarang;
use App\Http\Livewire\RekamMedis\LaporanStatistik;
use App\Http\Livewire\User\Manage as ManageUser;
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
        Route::get('/', AdminController::class)->name('dashboard');

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('darurat-stok', DaruratStokFarmasiController::class)
                    ->name('darurat-stok');

                Route::get('penggunaan-obat-perdokter', PenggunaanObatPerdokter::class)
                    ->middleware('can:farmasi.penggunaan-obat-perdokter.read')
                    ->name('obat-perdokter');
                
                Route::get('laporan-tahunan', LaporanProduksiTahunan::class)
                    ->middleware('can:farmasi.laporan-tahunan.read')
                    ->name('laporan-tahunan');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', LaporanStatistik::class)
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
            ->group(function () {
                Route::get('/', ManageUser::class)
                    ->middleware([
                        'can:user.manage',
                        'can:user.update',
                    ])
                    ->name('manage');
            });
    });