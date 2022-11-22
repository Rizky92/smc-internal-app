<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Farmasi\DataTable\LaporanPenggunaanObatPerDokterDataTableController;
use App\Http\Controllers\Khanza\Auth\LoginController;
use App\Http\Controllers\Khanza\Auth\LogoutController;
use App\Http\Controllers\Farmasi\LaporanDaruratStokController;
use App\Http\Controllers\Farmasi\LaporanPenggunaanObatPerDokterController;
use App\Http\Controllers\Farmasi\LaporanTahunanController;
use App\Http\Controllers\RekamMedis\LaporanStatistikPasienController;
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

Route::get('/', function () {
    return view('home');
});

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

        Route::prefix('datatable')
            ->as('datatable.')
            ->group(function () {
                Route::get('penggunaan-obat-perdokter', LaporanPenggunaanObatPerDokterDataTableController::class)->name('obat-perdokter');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('darurat-stok', LaporanDaruratStokController::class)->name('darurat-stok');
                Route::get('penggunaan-obat-perdokter', LaporanPenggunaanObatPerDokterController::class)->name('obat-perdokter');
                Route::get('laporan-tahunan', LaporanTahunanController::class)->name('laporan-tahunan');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', LaporanStatistikPasienController::class)->name('laporan-statistik');
            });
    });