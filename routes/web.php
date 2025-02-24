<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LogoutOtherSessionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrintLayoutController;
use App\Livewire\Antrean;
use App\Livewire\AntreanPintu;
use App\Livewire\Pages\Admin;
use App\Livewire\Pages\Antrean\AntreanPerPintu;
use App\Livewire\Pages\Antrean\AntreanPoli;
use App\Livewire\Pages\Antrian;
use App\Livewire\Pages\Aplikasi;
use App\Livewire\Pages\Farmasi;
use App\Livewire\Pages\HakAkses;
use App\Livewire\Pages\Informasi;
use App\Livewire\Pages\Keuangan;
use App\Livewire\Pages\Laboratorium;
use App\Livewire\Pages\Logistik;
use App\Livewire\Pages\Perawatan;
use App\Livewire\Pages\RekamMedis;
use App\Livewire\Pages\User;
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

Route::get('/antrean', Antrean::class)->name('antrean');
Route::get('/antrean-pintu', AntreanPintu::class)->name('antrean-pintu');
Route::get('/antrean/{kd_poli}', AntreanPoli::class)->name('antrean-poli');
Route::get('/antrean-per-pintu/{kd_pintu}', AntreanPerPintu::class)->name('antrean-per-pintu');
Route::get('/display-jadwal-dokter', Informasi\DisplayJadwalDokter::class);

Route::get('/print-layout', [PrintLayoutController::class, 'index']);

Route::get('/informasi-kamar', Informasi\InformasiKamar::class);

Route::get('/jadwal-dokter', Informasi\JadwalDokter::class);

Route::get('admin/antrian-poli/{kd_poli}/{kd_dokter}', Antrian\AntrianPoli::class)
    ->name('admin.antrian-poli');

Route::post('/antrian-poli/check-data-changes/{kd_poli}/{kd_dokter}', [Antrian\AntrianPoli::class, 'checkDataChanges'])
    ->name('antrian-poli.checkDataChanges');

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);

Route::post('logout', LogoutController::class)
    ->name('logout')
    ->middleware('auth');

Route::get('logout-other-device', [LogoutOtherSessionsController::class, 'show'])
    ->name('logout-other-device')
    ->middleware('auth');

Route::delete('logout-other-device', [LogoutOtherSessionsController::class, 'destroy'])
    ->middleware(['auth', 'password.confirm']);

Route::prefix('admin')
    ->middleware('auth')
    ->as('admin.')
    ->group(function () {
        Route::impersonate();

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::prefix('aplikasi')
            ->as('aplikasi.')
            ->group(function () {
                Route::get('bidang-unit', Aplikasi\BidangUnit::class)
                    ->name('bidang-unit')
                    ->middleware('can:aplikasi.bidang-unit.read');

                Route::get('pengaturan', Aplikasi\Pengaturan::class)
                    ->name('pengaturan')
                    ->middleware('canany:'.Aplikasi\Pengaturan::permissions());
            });

        Route::prefix('perawatan')
            ->as('perawatan.')
            ->group(function () {
                Route::get('daftar-pasien-ranap', Perawatan\DaftarPasienRanap::class)
                    ->name('daftar-pasien-ranap')
                    ->middleware('can:perawatan.daftar-pasien-ranap.read');

                Route::get('laporan-pasien-ranap', Perawatan\LaporanPasienRanap::class)
                    ->name('laporan-pasien-ranap')
                    ->middleware('can:perawatan.laporan-pasien-ranap.read');

                Route::get('laporan-transaksi-gantung', Perawatan\LaporanTransaksiGantung::class)
                    ->name('laporan-transaksi-gantung')
                    ->middleware('can:perawatan.laporan-transaksi-gantung.read');

                Route::get('laporan-hasil-pemeriksaan', Perawatan\LaporanHasilPemeriksaan::class)
                    ->name('laporan-hasil-pemeriksaan')
                    ->middleware('can:perawatan.laporan-hasil-pemeriksaan.read');
            });

        Route::prefix('laboratorium')
            ->as('lab.')
            ->group(function () {
                Route::get('hasil-mcu-karyawan', Laboratorium\KirimHasilMCUKaryawan::class)
                    ->name('hasil-mcu-karyawan')
                    ->middleware('can:lab.hasil-mcu-karyawan.read');
            });

        Route::prefix('keuangan')
            ->as('keuangan.')
            ->group(function () {
                Route::get('rkat-pelaporan', Keuangan\RKATPelaporan::class)
                    ->name('rkat-pelaporan')
                    ->middleware('can:keuangan.rkat-pelaporan.read');

                Route::get('rkat-pemantauan', Keuangan\RKATPemantauan::class)
                    ->name('rkat-pemantauan')
                    ->middleware('can:keuangan.rkat-pemantauan.read');

                Route::get('rkat-penetapan', Keuangan\RKATPenetapan::class)
                    ->name('rkat-penetapan')
                    ->middleware('can:keuangan.rkat-penetapan.read');

                Route::get('rkat-kategori', Keuangan\RKATKategori::class)
                    ->name('rkat-kategori')
                    ->middleware('can:keuangan.rkat-kategori.read');

                Route::get('stok-obat-ruangan', Keuangan\StokObatRuangan::class)
                    ->name('stok-obat-ruangan')
                    ->middleware('can:keuangan.stok-obat-ruangan.read');

                Route::get('laporan-tambahan-biaya-pasien', Keuangan\LaporanTambahanBiayaPasien::class)
                    ->name('laporan-tambahan-biaya')
                    ->middleware('can:keuangan.laporan-tambahan-biaya.read');

                Route::get('laporan-potongan-biaya-pasien', Keuangan\LaporanPotonganBiayaPasien::class)
                    ->name('laporan-potongan-biaya')
                    ->middleware('can:keuangan.laporan-potongan-biaya.read');

                Route::get('laporan-selesai-billing-pasien', Keuangan\LaporanSelesaiBillingPasien::class)
                    ->name('laporan-selesai-billing')
                    ->middleware('can:keuangan.laporan-selesai-billing.read');

                Route::get('jurnal-supplier-po', Keuangan\JurnalSupplierPO::class)
                    ->name('jurnal-supplier-po')
                    ->middleware('can:keuangan.jurnal-po-supplier.read');

                Route::get('jurnal-piutang-lunas', Keuangan\JurnalPiutangLunas::class)
                    ->name('jurnal-piutang-lunas')
                    ->middleware('can:keuangan.jurnal-piutang-lunas.read');

                Route::get('buku-besar', Keuangan\BukuBesar::class)
                    ->name('buku-besar')
                    ->middleware('can:keuangan.buku-besar.read');

                Route::get('laba-rugi-rekening-per-periode', Keuangan\LabaRugiRekeningPerPeriode::class)
                    ->name('laba-rugi-rekening')
                    ->middleware('can:keuangan.laba-rugi-rekening.read');

                Route::get('jurnal-perbaikan', Keuangan\JurnalPerbaikan::class)
                    ->name('jurnal-perbaikan')
                    ->middleware('can:keuangan.jurnal-perbaikan.read');

                Route::get('jurnal-perbaikan-riwayat', Keuangan\JurnalPerbaikanRiwayat::class)
                    ->name('jurnal-perbaikan-riwayat')
                    ->middleware('can:keuangan.jurnal-perbaikan-riwayat.read');

                Route::get('laporan-tindakan-lab', Keuangan\LaporanTindakanLab::class)
                    ->name('laporan-tindakan-lab')
                    ->middleware('can:keuangan.laporan-tindakan-lab.read');

                Route::get('laporan-tindakan-radiologi', Keuangan\LaporanTindakanRadiologi::class)
                    ->name('laporan-tindakan-radiologi')
                    ->middleware('can:keuangan.laporan-tindakan-radiologi.read');

                Route::get('account-receivable', Keuangan\AccountReceivable::class)
                    ->name('account-receivable')
                    ->middleware('can:keuangan.account-receivable.read');

                Route::get('account-payable', Keuangan\AccountPayable::class)
                    ->name('account-payable')
                    ->middleware('canany:keuangan.account-payable.read-medis|keuangan.account-payable.read-nonmedis');

                Route::get('laporan-trial-balance', Keuangan\LaporanTrialBalance::class)
                    ->name('laporan-trial-balance')
                    ->middleware('can:keuangan.laporan-trial-balance.read');

                Route::get('posting-jurnal', Keuangan\JurnalPosting::class)
                    ->name('posting-jurnal')
                    ->middleware('can:keuangan.posting-jurnal.read');

                Route::get('cetak-posting-jurnal', Keuangan\Cetak\HasilPostingJurnal::class)
                    ->name('cetak-posting-jurnal')
                    ->middleware('can:keuangan.posting-jurnal.read');

                Route::get('laporan-faktur-pajak-bpjs', Keuangan\LaporanFakturPajakBPJS::class)
                    ->name('laporan-faktur-pajak-bpjs')
                    ->middleware('can:keuangan.laporan-faktur-pajak.read');

                Route::get('laporan-faktur-pajak-asper', Keuangan\LaporanFakturPajakAsuransiPerusahaan::class)
                    ->name('laporan-faktur-pajak-asper')
                    ->middleware('can:keuangan.laporan-faktur-pajak.read');

                Route::get('laporan-faktur-pajak-umum', Keuangan\LaporanFakturPajakUmum::class)
                    ->name('laporan-faktur-pajak-umum')
                    ->middleware('can:keuangan.laporan-faktur-pajak.read');
            });

        Route::prefix('farmasi')
            ->as('farmasi.')
            ->group(function () {
                Route::get('defecta-depo', Farmasi\DefectaDepo::class)
                    ->name('defecta-depo')
                    ->middleware('can:farmasi.defecta-depo.read');

                Route::get('stok-darurat', Farmasi\RencanaOrder::class)
                    ->name('stok-darurat')
                    ->middleware('can:farmasi.stok-darurat.read');

                Route::get('pemakaian-stok', Farmasi\PemakaianStokFarmasi::class)
                    ->name('pemakaian-stok')
                    ->middleware('can:farmasi.pemakaian-stok.read');

                Route::get('penggunaan-obat-per-dokter', Farmasi\ObatPerDokter::class)
                    ->name('obat-per-dokter')
                    ->middleware('can:farmasi.obat-per-dokter.read');

                Route::get('laporan-produksi-tahunan', Farmasi\LaporanProduksiTahunan::class)
                    ->name('laporan-produksi')
                    ->middleware('can:farmasi.laporan-produksi.read');

                Route::get('kunjungan-resep-per-bentuk-obat', Farmasi\KunjunganPerBentukObat::class)
                    ->name('kunjungan-per-bentuk-obat')
                    ->middleware('can:farmasi.kunjungan-per-bentuk-obat.read');

                Route::get('kunjungan-resep-per-poli', Farmasi\KunjunganPerPoli::class)
                    ->name('kunjungan-per-poli')
                    ->middleware('can:farmasi.kunjungan-per-poli.read');

                Route::get('perbandingan-barang-po', Farmasi\PerbandinganBarangPO::class)
                    ->name('perbandingan-po-obat')
                    ->middleware('can:farmasi.perbandingan-po-obat.read');

                Route::get('laporan-pembuatan-soap', Farmasi\LaporanPembuatanSOAP::class)
                    ->name('laporan-pembuatan-soap')
                    ->middleware('can:farmasi.laporan-pembuatan-soap.read');

                Route::get('laporan-pemakaian-obat-napza', Farmasi\LaporanPemakaianObatNAPZA::class)
                    ->name('laporan-pemakaian-obat-napza')
                    ->middleware('can:farmasi.laporan-pemakaian-obat-napza.read');

                Route::get('laporan-pemakaian-obat-morphine', Farmasi\LaporanPemakaianObatMorphine::class)
                    ->name('laporan-pemakaian-obat-morphine')
                    ->middleware('can:farmasi.laporan-pemakaian-obat-morphine.read');

                Route::get('laporan-pemakaian-obat-tb', Farmasi\LaporanPemakaianObatTB::class)
                    ->name('laporan-pemakaian-obat-tb')
                    ->middleware('can:farmasi.laporan-pemakaian-obat-tb.read');

                Route::get('daftar-riwayat-obat-alkes', Farmasi\DaftarRiwayatObatAlkes::class)
                    ->name('daftar-riwayat-obat-alkes')
                    ->middleware('can:farmasi.daftar-riwayat-obat-alkes.read');

                Route::get('rincian-perbandingan-barang-po', Farmasi\RincianPerbandinganBarangPO::class)
                    ->name('rincian-perbandingan-po')
                    ->middleware('can:farmasi.rincian-perbandingan-po.read');

                Route::get('rincian-kunjungan-ralan', Farmasi\RincianKunjunganRalan::class)
                    ->name('rincian-kunjungan-ralan')
                    ->middleware('can:farmasi.rincian-kunjungan-ralan.read');
            });

        Route::prefix('rekam-medis')
            ->as('rekam-medis.')
            ->group(function () {
                Route::get('laporan-statistik', RekamMedis\LaporanStatistik::class)
                    ->name('laporan-statistik')
                    ->middleware('can:rekam-medis.laporan-statistik.read');

                Route::get('laporan-demografi', RekamMedis\LaporanDemografi::class)
                    ->name('laporan-demografi')
                    ->middleware('can:rekam-medis.laporan-demografi.read');

                Route::get('status-data-pasien', RekamMedis\StatusDataPasien::class)
                    ->name('status-data-pasien')
                    ->middleware('can:rekam-medis.status-data-pasien.read');
            });

        Route::prefix('antrean')
            ->as('antrean.')
            ->group(function () {
                Route::get('manajemen-pintu', Aplikasi\ManajemenPintu::class)
                    ->name('manajemen-pintu')
                    ->middleware('can:antrean.manajemen-pintu.read');
            });

        Route::prefix('informasi')
            ->as('informasi.')
            ->group(function () {
                Route::get('informasi-kamar', Informasi\InformasiKamar::class)
                    ->name('informasi-kamar')
                    ->middleware('can:informasi.informasi-kamar.read');

                Route::get('jadwal-dokter', Informasi\JadwalDokter::class)
                    ->name('jadwal-dokter');
            });

        Route::prefix('logistik')
            ->as('logistik.')
            ->group(function () {
                Route::get('input-minmax-stok', Logistik\InputMinmaxStok::class)
                    ->name('input-minmax-stok')
                    ->middleware('can:logistik.input-minmax-stok.read');

                Route::get('stok-darurat', Logistik\StokDaruratLogistik::class)
                    ->name('stok-darurat')
                    ->middleware('can:logistik.stok-darurat.read');
            });

        Route::middleware('role:'.config('permission.superadmin_name'))
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

                Route::get('job-cleaner', Admin\JobCleaner::class)
                    ->name('job-cleaner');
            });
    });
