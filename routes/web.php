<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\BPJS\MobileJKNController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Farmasi;
use App\Http\Livewire\HakAkses;
use App\Http\Livewire\Keuangan;
use App\Http\Livewire\Logistik;
use App\Http\Livewire\Perawatan;
use App\Http\Livewire\RekamMedis;
use App\Http\Livewire\User;
use Illuminate\Support\Facades\Route;
use InfyOm\RoutesExplorer\RoutesExplorer;
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

Route::get('tes-mobilejkn', MobileJKNController::class)->name('mobilejkn');

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
        Route::impersonate();

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::prefix('perawatan')
            ->as('perawatan.')
            ->group(function () {
                Route::get('daftar-pasien-ranap', Perawatan\DaftarPasienRanap::class)
                    ->middleware('can:perawatan.daftar-pasien-ranap.read')
                    ->name('daftar-pasien-ranap');

                Route::get('laporan-pasien-ranap', Perawatan\LaporanPasienRanap::class)
                    ->middleware('can:perawatan.laporan-pasien-ranap.read')
                    ->name('laporan-pasien-ranap');
            });

        Route::prefix('keuangan')
            ->as('keuangan.')
            ->group(function () {
                Route::get('stok-obat-ruangan', Keuangan\StokObatRuangan::class)
                    ->middleware('can:keuangan.stok-obat-ruangan.read')
                    ->name('stok-obat-ruangan');

                Route::get('rekap-piutang-pasien', Keuangan\RekapPiutangPasien::class)
                    ->middleware('can:keuangan.rekap-piutang-pasien.read')
                    ->name('rekap-piutang-pasien');

                Route::get('laporan-tambahan-biaya-pasien', Keuangan\LaporanTambahanBiayaPasien::class)
                    ->middleware('can:keuangan.laporan-tambahan-biaya.read')
                    ->name('laporan-tambahan-biaya');

                Route::get('laporan-potongan-biaya-pasien', Keuangan\LaporanPotonganBiayaPasien::class)
                    ->middleware('can:keuangan.laporan-potongan-biaya.read')
                    ->name('laporan-potongan-biaya');

                Route::get('laporan-selesai-billing-pasien', Keuangan\LaporanSelesaiBillingPasien::class)
                    ->middleware('can:keuangan.laporan-selesai-billing.read')
                    ->name('laporan-selesai-billing');

                Route::get('jurnal-supplier-po', Keuangan\JurnalSupplierPO::class)
                    ->middleware('can:keuangan.jurnal-po-supplier.read')
                    ->name('jurnal-supplier-po');

                Route::get('jurnal-piutang-lunas', Keuangan\JurnalPiutangLunas::class)
                    ->middleware('can:keuangan.jurnal-piutang-lunas.read')
                    ->name('jurnal-piutang-lunas');

                Route::get('buku-besar', Keuangan\BukuBesar::class)
                    ->middleware('can:keuangan.buku-besar.read')
                    ->name('buku-besar');

                Route::get('laba-rugi-rekening-per-periode', Keuangan\LabaRugiRekeningPerPeriode::class)
                    ->middleware('can:keuangan.laba-rugi-rekening.read')
                    ->name('laba-rugi-rekening');

                Route::get('dpjp-piutang-ranap', Keuangan\DPJPPiutangRanap::class)
                    ->middleware('can:keuangan.dpjp-piutang-ranap.read')
                    ->name('dpjp-piutang-ranap');

                Route::get('jurnal-perbaikan', Keuangan\JurnalPerbaikan::class)
                    ->middleware('can:keuangan.jurnal-perbaikan.read')
                    ->name('jurnal-perbaikan');

                Route::get('riwayat-jurnal-perbaikan', Keuangan\RiwayatJurnalPerbaikan::class)
                    ->middleware('can:keuangan.riwayat-jurnal-perbaikan.read')
                    ->name('riwayat-jurnal-perbaikan');

                Route::get('laporan-tindakan-lab', Keuangan\LaporanTindakanLab::class)
                    ->middleware('can:keuangan.laporan-tindakan-lab.read')
                    ->name('laporan-tindakan-lab');

                Route::get('laporan-tindakan-radiologi', Keuangan\LaporanTindakanRadiologi::class)
                    ->middleware('can:keuangan.laporan-tindakan-radiologi.read')
                    ->name('laporan-tindakan-radiologi');

                Route::get('account-receivable', Keuangan\AccountReceivable::class)
                    ->middleware('can:keuangan.account-receivable.read')
                    ->name('account-receivable');

                Route::get('account-payable', Keuangan\AccountPayable::class)
                    ->middleware('can:keuangan.account-payable.read')
                    ->name('account-payable');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('stok-darurat', Farmasi\StokDaruratFarmasi::class)
                    ->middleware('can:farmasi.stok-darurat.read')
                    ->name('stok-darurat');

                Route::get('penggunaan-obat-per-dokter', Farmasi\ObatPerDokter::class)
                    ->middleware('can:farmasi.obat-per-dokter.read')
                    ->name('obat-per-dokter');

                Route::get('laporan-produksi-tahunan', Farmasi\LaporanProduksiTahunan::class)
                    ->middleware('can:farmasi.laporan-produksi.read')
                    ->name('laporan-produksi');

                Route::get('kunjungan-resep-per-bentuk-obat', Farmasi\KunjunganPerBentukObat::class)
                    ->middleware('can:farmasi.kunjungan-per-bentuk-obat.read')
                    ->name('kunjungan-per-bentuk-obat');

                Route::get('kunjungan-resep-per-poli', Farmasi\KunjunganPerPoli::class)
                    ->middleware('can:farmasi.kunjungan-per-poli.read')
                    ->name('kunjungan-per-poli');

                Route::get('perbandingan-barang-po', Farmasi\PerbandinganBarangPO::class)
                    ->middleware('can:farmasi.perbandingan-po-obat.read')
                    ->name('perbandingan-po-obat');
                    
                Route::get('penyerahan-obat-drive-thru', Farmasi\PenyerahanObatNonResep::class)
                    ->middleware('can:farmasi.penyerahan-obat-drivethru.read')
                    ->name('penyerahan-obat-drivethru');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', RekamMedis\LaporanStatistik::class)
                    ->middleware('can:rekam-medis.laporan-statistik.read')
                    ->name('laporan-statistik');

                Route::get('laporan-demografi', RekamMedis\LaporanDemografi::class)
                    ->middleware('can:rekam-medis.laporan-demografi.read')
                    ->name('laporan-demografi');

                Route::get('status-data-pasien', RekamMedis\StatusDataPasien::class)
                    ->middleware('can:rekam-medis.status-data-pasien.read')
                    ->name('status-data-pasien');
            });

        Route::prefix('logistik')
            ->as('logistik.')
            ->group(function () {
                Route::get('input-minmax-stok', Logistik\InputMinmaxStok::class)
                    ->middleware('can:logistik.input-minmax-stok.read')
                    ->name('input-minmax-stok');

                Route::get('stok-darurat', Logistik\StokDaruratLogistik::class)
                    ->middleware('can:logistik.stok-darurat.read')
                    ->name('stok-darurat');
            });

        Route::middleware('role:' . config('permission.superadmin_name'))
            ->group(function () {
                Route::get('manajemen-user', User\ManajemenUser::class)
                    ->name('manajemen-user');

                Route::get('hak-akses/smc-internal-app', HakAkses\Siap::class)
                    ->name('hak-akses.siap');

                Route::get('hak-akses/simrs-khanza', HakAkses\Khanza::class)
                    ->name('hak-akses.khanza');

                Route::get('logs', [LogViewerController::class, 'index'])
                    ->name('log-viewer');

                Route::get('route-list', [RoutesExplorer::class, 'showRoutes'])
                    ->name('route-list');
            });
    });
