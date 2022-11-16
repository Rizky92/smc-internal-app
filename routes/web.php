<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Khanza\Auth\LoginController;
use App\Http\Controllers\Khanza\Auth\LogoutController;
use App\Http\Controllers\LaporanDaruratStokController;
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

        Route::get('farmasi/darurat-stok', [LaporanDaruratStokController::class, 'index'])->name('laporan.index');
    });