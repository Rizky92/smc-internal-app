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
use App\Models\Farmasi\ReturObatDetail;
use App\Models\Keuangan\FakturPajakDitarik;
use App\Models\Keuangan\FakturPajakDitarikDetail;
use App\Models\Keuangan\Master\SatuanUkuranPajak;
use App\Models\Keuangan\TambahanBiaya;
use App\Models\Laboratorium\PeriksaLab;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LaporanFakturPajakUmum extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $tanggalTarikan;

    protected function queryString(): array
    {
        return [
            'tglAwal'        => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'tanggalTarikan' => ['except' => '-', 'as' => 'tgl_tarik'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDataLaporanFakturPajakProperty()
    {
        if ($this->isDeferred) return [];

        if ($this->tanggalTarikan !== '-') {
            return FakturPajakDitarik::query()
                ->where('menu', 'fp-umum')
                ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                ->search($this->cari)
                ->paginate($this->perpage, ['*'], 'page_faktur');
        }

        $smc = DB::connection('mysql_smc')->getDatabaseName();

        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->laporanFakturPajakUmum($this->tglAwal, $this->tglAkhir)
            ->whereNotExists(fn ($q) => $q->from($smc.'.faktur_pajak_ditarik')
                ->whereColumn($smc.'.faktur_pajak_ditarik.no_rawat', 'reg_periksa.no_rawat')
                ->whereColumn($smc.'.faktur_pajak_ditarik.tgl_bayar', 'nota_bayar.tanggal'))
            ->search($this->cari)
            ->orderBy('reg_periksa.no_rawat')
            ->orderByDesc('kode_transaksi_pajak.kode_transaksi')
            ->paginate($this->perpage, ['*'], 'page_faktur');
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDataDetailFakturPajakProperty()
    {
        if ($this->isDeferred) return [];

        if ($this->tanggalTarikan !== '-') {
            return FakturPajakDitarik::query()
                ->where('menu', 'fp-umum')
                ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                ->paginate($this->perpage, ['*'], 'page_detailfaktur');
        }

        $kodeTransaksi = RegistrasiPasien::query()
            ->filterFakturPajak($this->tglAwal, $this->tglAkhir, 'A09')
            ->search($this->cari);

        $subQuery = RegistrasiPasien::query()->itemFakturPajakBiayaRegistrasi()
            ->unionAll(KamarInap::query()->itemFakturPajak())
            ->unionAll(TindakanRalanDokter::query()->itemFakturPajak())
            ->unionAll(TindakanRalanPerawat::query()->itemFakturPajak())
            ->unionAll(TindakanRalanDokterPerawat::query()->itemFakturPajak())
            ->unionAll(TindakanRanapDokter::query()->itemFakturPajak())
            ->unionAll(TindakanRanapPerawat::query()->itemFakturPajak())
            ->unionAll(TindakanRanapDokterPerawat::query()->itemFakturPajak())
            ->unionAll(PeriksaLab::query()->itemFakturPajak())
            // ->unionAll(PeriksaLabDetail::query()->itemFakturPajak()) // <-- nyalakan apabila terdapat tarif detail pemeriksaan lab
            ->unionAll(PeriksaRadiologi::query()->itemFakturPajak())
            ->unionAll(Operasi::query()->itemFakturPajak())
            ->unionAll(TambahanBiaya::query()->itemFakturPajak())
            ->unionAll(RegistrasiPasien::query()->itemFakturPajakTambahanEmbalaseTuslah())
            ->unionAll(PemberianObat::query()->itemFakturPajak('A09'))
            ->unionAll(ObatPulang::query()->itemFakturPajak('A09'))
            ->unionAll(ReturObatDetail::query()->itemFakturPajak('A09'));

        return DB::connection('mysql_sik')
            ->query()
            ->withExpression('regist_faktur', $kodeTransaksi)
            ->fromSub($subQuery, 'item_faktur_pajak')
            ->join('regist_faktur', 'item_faktur_pajak.no_rawat', '=', 'regist_faktur.no_rawat')
            ->orderBy('item_faktur_pajak.no_rawat')
            ->orderBy('item_faktur_pajak.urutan')
            ->orderBy('item_faktur_pajak.nama_barang_jasa')
            ->paginate($this->perpage, ['*'], 'page_detailfaktur');
    }

    public function getDataTanggalTarikanProperty(): Collection
    {
        return FakturPajakDitarik::query()
            ->selectRaw('distinct(tgl_tarikan) as tgl_tarikan')
            ->where('menu', 'fp-umum')
            ->pluck('tgl_tarikan', 'tgl_tarikan');
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
        $this->tanggalTarikan = '-';
    }

    /**
     * @return array<array-key, \Closure(): \Illuminate\Support\LazyCollection>
     */
    protected function dataPerSheet(): array
    {
        $tanggalTarikanSementara = $this->tanggalTarikan;
        
        if ($tanggalTarikanSementara === '-') {
            $tanggalTarikanSementara = now()->toDateTimeString();
            
            RegistrasiPasien::query()
                ->laporanFakturPajakUmum($this->tglAwal, $this->tglAkhir)
                ->orderBy('reg_periksa.no_rawat')
                ->orderByDesc('kode_transaksi_pajak.kode_transaksi')
                ->cursor()
                ->each(function (RegistrasiPasien $model) use ($tanggalTarikanSementara) {
                    $model->setAttribute('tgl_tarikan', $tanggalTarikanSementara);
                    $model->setAttribute('menu', 'fp-umum');
                    
                    FakturPajakDitarik::insert($model->toArray());
                });
    
            $kodeTransaksi = RegistrasiPasien::query()
                ->filterFakturPajak($this->tglAwal, $this->tglAkhir, 'A09')
                ->search($this->cari);
    
            $satuanUkuranPajak = SatuanUkuranPajak::pluck('kode_satuan_pajak', 'kode_sat');
    
            $subQuery = RegistrasiPasien::query()->itemFakturPajakBiayaRegistrasi()
                ->unionAll(KamarInap::query()->itemFakturPajak())
                ->unionAll(TindakanRalanDokter::query()->itemFakturPajak())
                ->unionAll(TindakanRalanPerawat::query()->itemFakturPajak())
                ->unionAll(TindakanRalanDokterPerawat::query()->itemFakturPajak())
                ->unionAll(TindakanRanapDokter::query()->itemFakturPajak())
                ->unionAll(TindakanRanapPerawat::query()->itemFakturPajak())
                ->unionAll(TindakanRanapDokterPerawat::query()->itemFakturPajak())
                ->unionAll(PeriksaLab::query()->itemFakturPajak())
                // ->unionAll(PeriksaLabDetail::query()->itemFakturPajak()) // <-- nyalakan apabila terdapat tarif detail pemeriksaan lab
                ->unionAll(PeriksaRadiologi::query()->itemFakturPajak())
                ->unionAll(Operasi::query()->itemFakturPajak())
                ->unionAll(TambahanBiaya::query()->itemFakturPajak())
                ->unionAll(RegistrasiPasien::query()->itemFakturPajakTambahanEmbalaseTuslah())
                ->unionAll(PemberianObat::query()->itemFakturPajak('A09'))
                ->unionAll(ObatPulang::query()->itemFakturPajak('A09'))
                ->unionAll(ReturObatDetail::query()->itemFakturPajak('A09'));

            DB::connection('mysql_sik')
                ->query()
                ->withExpression('regist_faktur', $kodeTransaksi)
                ->fromSub($subQuery, 'item_faktur_pajak')
                ->join('regist_faktur', 'item_faktur_pajak.no_rawat', '=', 'regist_faktur.no_rawat')
                ->orderBy('item_faktur_pajak.no_rawat')
                ->orderBy('item_faktur_pajak.urutan')
                ->orderBy('item_faktur_pajak.nama_barang_jasa')
                ->lazy()
                ->each(function (object $model) use ($satuanUkuranPajak, $tanggalTarikanSementara) {
                    $diskonPersen = round($model->diskon / ($model->totalbiaya + $model->diskon), 2);
                    $diskonNominal = round($diskonPersen * $model->dpp, 2);
                    $dppNilaiLain = round(($model->dpp - $diskonNominal) * (11/12), 2);
                    $ppnPersen = intval($model->ppn_persen === '0' ? '12' : $model->ppn_persen);
                    $ppnNominal = round($dppNilaiLain * ($ppnPersen / 100), 2);
                    
                    FakturPajakDitarikDetail::insert([
                        'no_rawat'           => $model->no_rawat,
                        'kode_transaksi'     => $model->kode_transaksi,
                        'tgl_bayar'          => $model->tgl_bayar,
                        'jam_bayar'          => $model->jam_bayar,
                        'tgl_tarikan'        => $tanggalTarikanSementara,
                        'menu'               => 'fp-umum',
                        'jenis_barang_jasa'  => $model->jenis_barang_jasa,
                        'kode_barang_jasa'   => $model->kode_barang_jasa,
                        'nama_barang_jasa'   => $model->nama_barang_jasa,
                        'nama_satuan_ukur'   => $satuanUkuranPajak->get($model->nama_satuan_ukur) ?? '',
                        'harga_satuan'       => $model->harga_satuan,
                        'jumlah_barang_jasa' => $model->jumlah_barang_jasa,
                        'diskon_persen'      => $diskonPersen,
                        'diskon_nominal'     => $diskonNominal,
                        'dpp'                => round(floatval($model->dpp), 2),
                        'dpp_nilai_lain'     => $dppNilaiLain,
                        'ppn_persen'         => $ppnPersen,
                        'ppn_nominal'        => $ppnNominal,
                        'ppnbm_persen'       => 0,
                        'ppnbm_nominal'      => 0,
                        'kd_jenis_prw'       => $model->kd_jenis_prw,
                        'kategori'           => $model->kategori,
                        'status_lanjut'      => $model->status_lanjut,
                    ]);
                });
                
            $this->tanggalTarikan = $tanggalTarikanSementara;
        }
        
        $this->isDeferred = true;
        
        return [
            'Faktur' => fn () => FakturPajakDitarik::query()
                ->whereBetween('tgl_tarikan', [$tanggalTarikanSementara, $tanggalTarikanSementara])
                ->cursor()
                ->map(fn (FakturPajakDitarik $model): array => [
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
                ]),
            'Detail Faktur' => fn () => FakturPajakDitarikDetail::query()
                ->whereBetween('tgl_tarikan', [$tanggalTarikanSementara, $tanggalTarikanSementara])
                ->withCasts([
                    'harga_satuan'       => 'float',
                    'jumlah_barang_jasa' => 'float',
                    'diskon_persen'      => 'float',
                    'diskon_nominal'     => 'float',
                    'dpp'                => 'float',
                    'dpp_nilai_lain'     => 'float',
                    'ppn_persen'         => 'float',
                    'ppn_nominal'        => 'float',
                    'ppnbm_persen'       => 'float',
                    'ppnbm_nominal'      => 'float,'
                ])
                ->cursor()
                ->map(fn (FakturPajakDitarikDetail $model): array => [
                    'no_rawat'           => $model->no_rawat,
                    'kode_transaksi'     => $model->kode_transaksi,
                    'tgl_bayar'          => $model->tgl_bayar,
                    'jenis_barang_jasa'  => $model->jenis_barang_jasa,
                    'kode_barang_jasa'   => $model->kode_barang_jasa,
                    'nama_barang_jasa'   => $model->nama_barang_jasa,
                    'nama_satuan_ukur'   => $model->nama_satuan_ukur,
                    'harga_satuan'       => $model->harga_satuan,
                    'jumlah_barang_jasa' => $model->jumlah_barang_jasa,
                    'diskon_nominal'     => $model->diskon_nominal,
                    'dpp'                => $model->dpp,
                    'dpp_nilai_lain'     => $model->dpp_nilai_lain,
                    'ppn_persen'         => $model->ppn_persen,
                    'ppn_nominal'        => $model->ppn_nominal,
                    'ppnbm_persen'       => 0,
                    'ppnbm_nominal'      => 0,
                    'kd_jenis_prw'       => $model->kd_jenis_prw,
                    'kategori'           => $model->kategori,
                    'status_lanjut'      => $model->status_lanjut,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Faktur' => [
                'No. Rawat',
                'Jenis Rawat',
                'Tgl. Faktur',
                'Jenis Faktur',
                'Kode Transaksi',
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
            'Detail Faktur' => [
                'No. Rawat',
                'Kode Transaksi',
                'Tgl. Faktur',
                'Barang/Jasa',
                'Kode Barang/Jasa',
                'Nama Barang/Jasa',
                'Nama Satuan Ukur',
                'Harga Satuan',
                'Jumlah Barang/Jasa',
                'Total Diskon',
                'DPP',
                'DPP Nilai Lain',
                'Tarif PPN',
                'PPN',
                'Tarif PPnBM',
                'PPnBM',
                'Kode Item RS',
                'Kategori',
                'Jenis Rawat',
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
            'Laporan Faktur Pajak Pasien BPJS KESEHATAN (BPJ)',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
