<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Farmasi\KunjunganPerBentukObat;
use App\Http\Livewire\Farmasi\KunjunganPerPoli;
use App\Http\Livewire\Farmasi\LaporanProduksiTahunan;
use App\Http\Livewire\Farmasi\ObatPerDokter;
use App\Http\Livewire\Farmasi\PerbandinganBarangPO;
use App\Http\Livewire\Farmasi\StokDaruratFarmasi;
use App\Http\Livewire\HakAkses\HakAksesCustomReport;
use App\Http\Livewire\HakAkses\HakAksesKhanza;
use App\Http\Livewire\Keuangan\RekapPiutangPasien;
use App\Http\Livewire\Keuangan\StokObatRuangan;
use App\Http\Livewire\Logistik\InputMinmaxStok;
use App\Http\Livewire\Logistik\StokDaruratLogistik;
use App\Http\Livewire\MOD\LaporanPasienRanap;
use App\Http\Livewire\Perawatan\DaftarPasienRanap;
use App\Http\Livewire\RekamMedis\LaporanDemografi;
use App\Http\Livewire\RekamMedis\LaporanStatistik;
use App\Http\Livewire\User\ManajemenUser;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

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

        Route::prefix('mod')
            ->as('mod.')
            ->group(function () {
                Route::get('laporan-pasien-ranap', LaporanPasienRanap::class)
                    ->middleware('can:mod.laporan-pasien-ranap.read')
                    ->name('laporan-pasien-ranap');
            });

        Route::prefix('perawatan')
            ->as('perawatan.')
            ->group(function () {
                Route::get('daftar-pasien-ranap', DaftarPasienRanap::class)
                    ->middleware('can:perawatan.daftar-pasien-ranap.read')
                    ->name('daftar-pasien-ranap');
            });

        Route::prefix('keuangan')
            ->as('keuangan.')
            ->group(function () {
                Route::get('stok-obat-ruangan', StokObatRuangan::class)
                    ->middleware('can:keuangan.stok-obat-ruangan.read')
                    ->name('stok-obat-ruangan');

                Route::get('rekap-piutang-pasien', RekapPiutangPasien::class)
                    ->middleware('can:keuangan.piutang-pasien.read')
                    ->name('piutang-pasien');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('stok-darurat', StokDaruratFarmasi::class)
                    ->middleware('can:farmasi.stok-darurat.read')
                    ->name('stok-darurat');

                Route::get('penggunaan-obat-per-dokter', ObatPerDokter::class)
                    ->middleware('can:farmasi.obat-per-dokter.read')
                    ->name('obat-per-dokter');

                Route::get('laporan-produksi-tahunan', LaporanProduksiTahunan::class)
                    ->middleware('can:farmasi.laporan-produksi.read')
                    ->name('laporan-produksi');

                Route::get('kunjungan-resep-per-bentuk-obat', KunjunganPerBentukObat::class)
                    ->middleware('can:farmasi.kunjungan-per-bentuk-obat.read')
                    ->name('kunjungan-per-bentuk-obat');

                Route::get('kunjungan-resep-per-poli', KunjunganPerPoli::class)
                    ->middleware('can:farmasi.kunjungan-per-poli.read')
                    ->name('kunjungan-per-poli');

                Route::get('ringkasan-perbandingan-po-obat', PerbandinganBarangPO::class)
                    ->middleware('can:farmasi.perbandingan-po-obat.read')
                    ->name('perbandingan-po-obat');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', LaporanStatistik::class)
                    ->middleware('can:rekam-medis.laporan-statistik.read')
                    ->name('laporan-statistik');

                Route::get('laporan-demografi', LaporanDemografi::class)
                    ->middleware('can:rekam-medis.laporan-demografi.read')
                    ->name('laporan-demografi');
            });

        Route::prefix('logistik')
            ->as('logistik.')
            ->group(function () {
                Route::get('input-minmax-stok', InputMinmaxStok::class)
                    ->middleware('can:logistik.input-minmax-stok.read')
                    ->name('input-minmax-stok');

                Route::get('stok-darurat', StokDaruratLogistik::class)
                    ->middleware('can:logistik.stok-darurat.read')
                    ->name('stok-darurat');
            });

        Route::middleware('role:' . config('permission.superadmin_name'))
            ->group(function () {
                Route::get('manajemen-user', ManajemenUser::class)
                    ->name('manajemen-user');

                Route::get('hak-akses/custom-report', HakAksesCustomReport::class)
                    ->name('hak-akses.custom-report');

                Route::get('hak-akses/khanza', HakAksesKhanza::class)
                    ->name('hak-akses.khanza');

                Route::get('logs', [LogViewerController::class, 'index'])
                    ->name('log-viewer');
            });
    });
