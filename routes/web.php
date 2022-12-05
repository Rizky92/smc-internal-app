<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Khanza\Auth\LoginController;
use App\Http\Controllers\Khanza\Auth\LogoutController;
use App\Http\Controllers\Farmasi\LaporanDaruratStokController as DaruratStokFarmasiController;
use App\Http\Controllers\Farmasi\LaporanTahunanController;
use App\Http\Controllers\Logistik\InputStokMinMaxController;
use App\Http\Controllers\Logistik\LaporanDaruratStokController as DaruratStokLogistikController;
use App\Http\Controllers\UserController;
use App\Http\Livewire\Farmasi\PenggunaanObatPerdokter;
use App\Http\Livewire\RekamMedis\LaporanStatistik;
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

Route::view('/', 'home');

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

        Route::get('pengguna', UserController::class)
            ->name('users');

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('darurat-stok', DaruratStokFarmasiController::class)
                    ->name('darurat-stok');

                // Route::get('penggunaan-obat-perdokter', LaporanPenggunaanObatPerDokterController::class)->name('obat-perdokter');
                Route::get('penggunaan-obat-perdokter', PenggunaanObatPerdokter::class)
                    // ->middleware('can:farmasi.penggunaan-obat-perdokter.read')
                    ->name('obat-perdokter');
                
                Route::get('laporan-tahunan', LaporanTahunanController::class)
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
                Route::get('darurat-stok', DaruratStokLogistikController::class)
                    ->middleware('can:logistik.darurat-stok.read')
                    ->name('darurat-stok');

                Route::resource('minmax', InputStokMinMaxController::class)
                    ->parameters('barang')
                    ->middleware([
                        'can:logistik.stok-minmax.read',
                        'can:logistik.stok-minmax.update',
                    ])
                    ->names('minmax');
            });
    });