<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\ObatPulang;
use App\Models\Farmasi\PemberianObat;
use App\Models\Keuangan\TambahanBiaya;
use App\Models\Laboratorium\PeriksaLab;
use App\Models\Laboratorium\PeriksaLabDetail;
use App\Models\Perawatan\KamarInap;
use App\Models\Perawatan\Operasi;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Perawatan\TindakanRalanDokter;
use App\Models\Perawatan\TindakanRalanDokterPerawat;
use App\Models\Perawatan\TindakanRalanPerawat;
use App\Models\Perawatan\TindakanRanapDokter;
use App\Models\Perawatan\TindakanRanapDokterPerawat;
use App\Models\Perawatan\TindakanRanapPerawat;
use App\Models\Radiologi\PeriksaRadiologi;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LaporanFakturPajakUmum extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanFakturPajakProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->laporanFakturPajakUmum($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_faktur');
    }

    public function getDataDetailFakturPajakProperty()
    {
        if ($this->isDeferred) return [];
        
        $subQuery = RegistrasiPasien::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir)
            ->unionAll(KamarInap::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanDokter::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanDokterPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapDokter::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapDokterPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaLab::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaLabDetail::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaRadiologi::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PemberianObat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(ObatPulang::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(Operasi::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TambahanBiaya::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir));

        return DB::connection('mysql_sik')
            ->query()
            ->fromSub($subQuery, 'item_faktur_pajak')
            ->orderBy('no_rawat')
            ->orderBy('urutan')
            ->paginate($this->perpage, ['*'], 'page_detailfaktur');
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak-umum')
            ->layout(BaseLayout::class, ['title' => 'Laporan Faktur Pajak Pasien UMUM / PERSONAL (A09)']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        $subQuery = RegistrasiPasien::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir)
            ->unionAll(KamarInap::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanDokter::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRalanDokterPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapDokter::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TindakanRanapDokterPerawat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaLab::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaLabDetail::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PeriksaRadiologi::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(PemberianObat::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(ObatPulang::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(Operasi::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir))
            ->unionAll(TambahanBiaya::query()->itemFakturPajak($this->tglAwal, $this->tglAkhir));

        return [
            'Faktur' => RegistrasiPasien::query()
                ->laporanFakturPajakBPJS($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'no_rawat'            => $model->no_rawat,
                    'status_lanjut'       => $model->status_lanjut,
                    'tgl_bayar'           => carbon($model->tgl_bayar)->format('d-m-Y'),
                    'jenis_faktur'        => $model->jenis_faktur,
                    'kode_transaksi'      => $model->kode_transaksi,
                    'keterangan_tambahan' => $model->keterangan_tambahan,
                    'dokumen_pendukung'   => $model->dokumen_pendukung,
                    'cap_fasilitas'       => $model->cap_fasilitas,
                    'id_tku_penjual'      => $model->id_tku_penjual,
                    'jenis_id'            => $model->jenis_id,
                    'negara'              => $model->negara,
                    'id_tku'              => $model->id_tku,
                    'no_rkm_medis'        => $model->no_rkm_medis,
                    'nik_pasien'          => $model->nik_pasien,
                    'nama_pasien'         => $model->nama_pasien,
                    'alamat_pasien'       => $model->alamat_pasien,
                    'email_pasien'        => $model->email_pasien,
                    'no_telp_pasien'      => $model->no_telp_pasien,
                    'kode_asuransi'       => $model->kode_asuransi,
                    'nama_asuransi'       => $model->nama_asuransi,
                ])
                ->all(),
            'Detail Faktur' => DB::connection('mysql_sik')
                ->query()
                ->fromSub($subQuery, 'item_faktur_pajak')
                ->orderBy('no_rawat')
                ->orderBy('urutan')
                ->cursor()
                ->map(function (object $model): array {
                    $dppNilaiLain = round(floatval($model->dpp) * (11/12), 2);
                    $ppn = intval($model->ppn_persen === '0' ? '12' : $model->ppn_persen);
                    $totalPpn = round($dppNilaiLain * ($ppn / 100), 2);
                    
                    return [
                        'no_rawat'           => $model->no_rawat,
                        'kd_jenis_prw'       => $model->kd_jenis_prw,
                        'kategori'           => $model->kategori,
                        'status_lanjut'      => $model->status_lanjut,
                        'jenis_barang_jasa'  => $model->jenis_barang_jasa,
                        'kode_barang_jasa'   => $model->kode_barang_jasa,
                        'nama_barang_jasa'   => $model->nama_barang_jasa,
                        'nama_satuan_ukur'   => $model->nama_satuan_ukur,
                        'harga_satuan'       => round($model->harga_satuan, 2),
                        'jumlah_barang_jasa' => round($model->jumlah_barang_jasa, 2),
                        'total_diskoon'      => round($model->diskon_nominal, 2),
                        'dpp'                => round($model->dpp, 2),
                        'dpp_nilai_lain'     => $dppNilaiLain,
                        'ppn'                => $ppn,
                        'total_ppn'          => $totalPpn,
                        'ppnbm'              => 0,
                        'tarif_ppnbm'        => 0,
                    ];
                })
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Faktur' => [
                'No. Rawat',
                'Kode Transaksi',
                'Tgl. Bayar',
                'Jam Bayar',
                'Jenis Rawat',
                'Jenis Faktur',
                'Keterangan Tambahan',
                'Dokumen Pendukung',
                'Cap Fasilitas',
                'ID TKU Penjual',
                'Jenis ID',
                'Negara',
                'ID TKU',
                'No. RM',
                'NIK Pasien',
                'Nama Pasien',
                'Alamat Pasien',
                'Email Pasien',
                'No. Telp Pasien',
                'Kode Asuransi',
                'Nama Asuransi',
            ],
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Faktur Pajak Pasien UMUM / PERSONAL (A09)',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
