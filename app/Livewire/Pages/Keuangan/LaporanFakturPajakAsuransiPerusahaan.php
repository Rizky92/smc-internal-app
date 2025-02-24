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
use App\Models\RekamMedis\Penjamin;
use App\Settings\NPWPSettings;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LaporanFakturPajakAsuransiPerusahaan extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var int */
    public $option;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $tanggalTarikan;

    /** @var string */
    public $npwpPenjual;

    /** @var Collection */
    public $satuanUkur;

    /** @var string */
    public $kodePJ;

    /** @var bool */
    public $isPerusahaan;

    /** @var int */
    private const FORMAT_RAW = 1;

    /** @var int */
    private const FORMAT_CORETAX = 2;

    protected function queryString(): array
    {
        return [
            'tglAwal'        => ['except' => now()->subDays(5)->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->toDateString(), 'as' => 'tgl_akhir'],
            'tanggalTarikan' => ['except' => '-', 'as' => 'tgl_tarik'],
            'kodePJ'         => ['except' => '-', 'as' => 'kode_pj'],
            'isPerusahaan'   => ['except' => false, 'as' => 'is_perusahaan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
        $this->npwpPenjual = app(NPWPSettings::class)->npwp_penjual;
        $this->satuanUkur = SatuanUkuranPajak::pluck('kode_satuan_pajak', 'kode_sat');
    }

    /**
     * @return array<empty, empty>|LengthAwarePaginator
     */
    public function getDataLaporanFakturPajakProperty()
    {
        if ($this->isDeferred) {
            return [];
        }

        if ($this->tanggalTarikan !== '-') {
            return FakturPajakDitarik::query()
                ->where('menu', 'fp-asper')
                ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                ->search($this->cari)
                ->paginate($this->perpage, ['*'], 'page_faktur');
        }

        $smc = DB::connection('mysql_smc')->getDatabaseName();

        return RegistrasiPasien::query()
            ->laporanFakturPajakAsuransi($this->tglAwal, $this->tglAkhir, $this->kodePJ, $this->isPerusahaan)
            ->whereNotExists(fn ($q) => $q->from($smc.'.faktur_pajak_ditarik')
                ->whereColumn($smc.'.faktur_pajak_ditarik.tgl_bayar', 'nota_bayar.tanggal')
                ->whereColumn($smc.'.faktur_pajak_ditarik.no_rawat', 'reg_periksa.no_rawat'))
            ->search($this->cari)
            ->orderBy('reg_periksa.no_rawat')
            ->orderByDesc('kode_transaksi_pajak.kode_transaksi')
            ->paginate($this->perpage, ['*'], 'page_faktur');
    }

    /**
     * @return array<empty, empty>|LengthAwarePaginator
     */
    public function getDataDetailFakturPajakProperty()
    {
        if ($this->isDeferred) {
            return [];
        }

        if ($this->tanggalTarikan !== '-') {
            return FakturPajakDitarikDetail::query()
                ->where('menu', 'fp-asper')
                ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                ->paginate($this->perpage, ['*'], 'page_detailfaktur');
        }

        $smc = DB::connection('mysql_smc')->getDatabaseName();

        $registFaktur = RegistrasiPasien::query()
            ->filterFakturPajak($this->tglAwal, $this->tglAkhir, $this->kodePJ, $this->isPerusahaan)
            ->whereNotExists(fn ($q) => $q->from($smc.'.faktur_pajak_ditarik_detail')
                ->whereColumn($smc.'.faktur_pajak_ditarik_detail.no_rawat', 'reg_periksa.no_rawat')
                ->whereColumn($smc.'.faktur_pajak_ditarik_detail.tgl_bayar', 'nota_bayar.tanggal'))
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
            ->unionAll(PemberianObat::query()->itemFakturPajak())
            ->unionAll(ObatPulang::query()->itemFakturPajak())
            ->unionAll(ReturObatDetail::query()->itemFakturPajak());

        return DB::connection('mysql_sik')
            ->query()
            ->withExpression('regist_faktur', $registFaktur)
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
            ->where('menu', 'fp-asper')
            ->pluck('tgl_tarikan', 'tgl_tarikan');
    }

    public function getDataPenjaminProperty(): Collection
    {
        return Penjamin::query()
            ->when($this->isPerusahaan, fn ($q) => $q->whereExists(fn ($q) => $q
                ->from('perusahaan_pasien')
                ->whereColumn('perusahaan_pasien.kode_perusahaan', 'penjab.kd_pj')))
            ->pluck('png_jawab', 'kd_pj');
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak-asuransi-perusahaan')
            ->layout(BaseLayout::class, ['title' => 'Laporan Faktur Pajak Pasien ASURANSI / PERUSAHAAN']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->subDays(5)->toDateString();
        $this->tglAkhir = now()->toDateString();
        $this->tanggalTarikan = '-';
        $this->kodePJ = '-';
        $this->isPerusahaan = false;
    }

    protected function simpanTarikan(): void
    {
        $tanggalTarikanSementara = $this->tanggalTarikan;

        if ($tanggalTarikanSementara !== '-') {
            return;
        }

        $tanggalTarikanSementara = now()->toDateTimeString();

        $smc = DB::connection('mysql_smc')->getDatabaseName();

        RegistrasiPasien::query()
            ->laporanFakturPajakAsuransi($this->tglAwal, $this->tglAkhir, $this->kodePJ, $this->isPerusahaan)
            ->whereNotExists(fn ($q) => $q->from($smc.'.faktur_pajak_ditarik')
                ->whereColumn($smc.'.faktur_pajak_ditarik.no_rawat', 'reg_periksa.no_rawat')
                ->whereColumn($smc.'.faktur_pajak_ditarik.tgl_bayar', 'nota_bayar.tanggal'))
            ->search($this->cari)
            ->orderBy('reg_periksa.no_rawat')
            ->orderByDesc('kode_transaksi_pajak.kode_transaksi')
            ->cursor()
            ->each(function (RegistrasiPasien $model) use ($tanggalTarikanSementara) {
                $model->setAttribute('jenis_id', 'TIN');
                $model->setAttribute('id_tku_penjual', $this->npwpPenjual);
                $model->setAttribute('tgl_tarikan', $tanggalTarikanSementara);
                $model->setAttribute('tgl_faktur', $this->tglAkhir);
                $model->setAttribute('menu', 'fp-asper');

                FakturPajakDitarik::insert($model->toArray());
            });

        $registFaktur = RegistrasiPasien::query()
            ->filterFakturPajak($this->tglAwal, $this->tglAkhir, $this->kodePJ, $this->isPerusahaan)
            ->whereNotExists(fn ($q) => $q->from($smc.'.faktur_pajak_ditarik_detail')
                ->whereColumn($smc.'.faktur_pajak_ditarik_detail.no_rawat', 'reg_periksa.no_rawat')
                ->whereColumn($smc.'.faktur_pajak_ditarik_detail.tgl_bayar', 'nota_bayar.tanggal'))
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
            ->unionAll(PemberianObat::query()->itemFakturPajak())
            ->unionAll(ObatPulang::query()->itemFakturPajak())
            ->unionAll(ReturObatDetail::query()->itemFakturPajak());

        $totalJasa = DB::connection('mysql_sik')
            ->query()
            ->withExpression('regist_faktur', $registFaktur)
            ->fromSub($subQuery, 'item_faktur_pajak')
            ->join('regist_faktur', 'item_faktur_pajak.no_rawat', '=', 'regist_faktur.no_rawat')
            ->selectRaw('item_faktur_pajak.no_rawat, sum(item_faktur_pajak.dpp) as subtotal')
            ->whereNotIn('kategori', ['Pemberian Obat', 'Retur Obat', 'Obat Pulang', 'Walk In', 'Piutang Obat'])
            ->groupBy('item_faktur_pajak.no_rawat')
            ->pluck('subtotal', 'no_rawat');

        DB::connection('mysql_sik')
            ->query()
            ->withExpression('regist_faktur', $registFaktur)
            ->fromSub($subQuery, 'item_faktur_pajak')
            ->join('regist_faktur', 'item_faktur_pajak.no_rawat', '=', 'regist_faktur.no_rawat')
            ->orderBy('item_faktur_pajak.no_rawat')
            ->orderBy('item_faktur_pajak.urutan')
            ->orderBy('item_faktur_pajak.nama_barang_jasa')
            ->cursor()
            ->each(function (object $model) use ($satuanUkuranPajak, $tanggalTarikanSementara, $totalJasa) {
                $diskonPersen = $model->diskon_persen;
                $diskonNominal = $model->diskon_nominal;
                $dpp = $model->dpp;

                if (! in_array($model->kategori, ['Pemberian Obat', 'Retur Obat', 'Obat Pulang', 'Walk In', 'Piutang Obat'])) {
                    $subtotalJasa = (float) $totalJasa->get($model->no_rawat, 1);
                    $diskonPersen = ((float) $model->diskon) / (round($subtotalJasa, 0) ?: 1);
                    $diskonNominal = $diskonPersen * ((float) $model->dpp);
                    $dpp = ((float) $model->dpp) - $diskonNominal;
                }

                $dppNilaiLain = $dpp * (11 / 12);
                $ppnPersen = (int) $model->ppn_persen;
                $ppnNominal = $dppNilaiLain * ($ppnPersen / 100);

                FakturPajakDitarikDetail::insert([
                    'no_rawat'           => $model->no_rawat,
                    'kode_transaksi'     => $model->kode_transaksi,
                    'tgl_bayar'          => $model->tgl_bayar,
                    'jam_bayar'          => $model->jam_bayar,
                    'tgl_tarikan'        => $tanggalTarikanSementara,
                    'menu'               => 'fp-asper',
                    'jenis_barang_jasa'  => $model->jenis_barang_jasa,
                    'kode_barang_jasa'   => $model->kode_barang_jasa,
                    'nama_barang_jasa'   => $model->nama_barang_jasa,
                    'nama_satuan_ukur'   => $satuanUkuranPajak->get($model->nama_satuan_ukur, 'UM.0033'),
                    'harga_satuan'       => (float) $model->harga_satuan,
                    'jumlah_barang_jasa' => (float) $model->jumlah_barang_jasa,
                    'diskon_persen'      => $diskonPersen,
                    'diskon_nominal'     => $diskonNominal,
                    'dpp'                => $dpp,
                    'dpp_nilai_lain'     => $dppNilaiLain,
                    'ppn_persen'         => $ppnPersen,
                    'ppn_nominal'        => $ppnNominal,
                    'ppnbm_persen'       => 0,
                    'ppnbm_nominal'      => 0,
                    'kd_jenis_prw'       => $model->kd_jenis_prw,
                    'kategori'           => $model->kategori,
                    'status_lanjut'      => $model->status_lanjut,
                    'kode_asuransi'      => $model->kd_pj,
                    'no_rkm_medis'       => $model->no_rkm_medis,
                ]);
            });

        $this->isDeferred = true;
        $this->forgetComputed('dataTanggalTarikan');
        $this->tanggalTarikan = $tanggalTarikanSementara;
        $this->dispatchBrowserEvent('data-tarikan:updated', ['tanggalTarikan' => $tanggalTarikanSementara]);
    }

    public function exportWithOption(int $option): void
    {
        $this->option = $option;

        $this->exportToExcel();
    }

    /**
     * @psalm-suppress UndefinedMethod
     */
    protected function dataPerSheet(): array
    {
        $this->simpanTarikan();

        /**
         * @psalm-suppress MissingClosureReturnType
         * @psalm-suppress InvalidReturnType
         */
        switch ($this->option) {
            case self::FORMAT_CORETAX:
                return [
                    'Faktur' => fn () => FakturPajakDitarik::query()
                        ->selectRaw(<<<'SQL'
                            dense_rank() over (order by kode_asuransi, kode_transaksi) as baris,
                            tgl_faktur,
                            jenis_faktur,
                            kode_transaksi,
                            keterangan_tambahan,
                            '' as dokumen_pendukung,
                            '' as referensi,
                            cap_fasilitas,
                            id_tku_penjual,
                            if (kode_asuransi = 'A09', '', npwp_asuransi) as npwp_nik,
                            jenis_id,
                            negara,
                            if (kode_asuransi = 'A09', nik_pasien, '') as nomor_dokumen,
                            if (kode_asuransi = 'A09', nama_pasien, nama_asuransi) as nama,
                            if (kode_asuransi = 'A09', alamat_pasien, alamat_asuransi) as alamat,
                            if (kode_asuransi = 'A09', email_pasien, email_asuransi) as email,
                            if (kode_asuransi = 'A09', '', rpad(trim(npwp_asuransi), 22, '0')) as id_tku
                            SQL)
                        ->where('menu', 'fp-asper')
                        ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                        ->groupBy(['tgl_faktur', 'kode_asuransi', 'kode_transaksi'])
                        ->cursor()
                        ->map(fn (FakturPajakDitarik $model): array => [
                            'baris'               => $model->baris,
                            'tgl_faktur'          => $model->tgl_faktur,
                            'jenis_faktur'        => $model->jenis_faktur,
                            'kode_transaksi'      => $model->kode_transaksi,
                            'keterangan_tambahan' => $model->keterangan_tambahan,
                            'dokumen_pendukung'   => $model->dokumen_pendukung,
                            'referensi'           => $model->referensi,
                            'cap_fasilitas'       => $model->cap_fasilitas,
                            'id_tku_penjual'      => $model->id_tku_penjual,
                            'npwp_nik'            => $model->npwp_nik,
                            'jenis_id'            => $model->jenis_id,
                            'negara'              => $model->negara,
                            'nomor_dokumen'       => $model->nomor_dokumen,
                            'nama'                => $model->nama,
                            'alamat'              => $model->alamat,
                            'email'               => $model->email,
                            'id_tku'              => $model->id_tku,
                        ]),
                    'Detail Faktur' => fn () => FakturPajakDitarikDetail::query()
                        ->selectRaw(<<<'SQL'
                            dense_rank() over (order by kode_asuransi, kode_transaksi) as baris,
                            jenis_barang_jasa,
                            kode_barang_jasa,
                            nama_barang_jasa,
                            nama_satuan_ukur,
                            harga_satuan,
                            sum(jumlah_barang_jasa) as jumlah_barang_jasa,
                            sum(diskon_nominal) as diskon_nominal,
                            sum(dpp) as dpp,
                            sum(dpp_nilai_lain) as dpp_nilai_lain,
                            ppn_persen,
                            sum(ppn_nominal) as ppn_nominal,
                            ppnbm_persen,
                            sum(ppnbm_nominal) as ppnbm_nominal
                            SQL)
                        ->where('menu', 'fp-asper')
                        ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
                        ->groupBy(['kode_asuransi', 'kode_transaksi', 'kategori', 'kd_jenis_prw', 'harga_satuan', 'ppn_persen'])
                        ->orderBy('kode_asuransi')
                        ->orderBy('kode_transaksi')
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
                            'ppnbm_nominal'      => 'float',
                        ])
                        ->cursor()
                        ->map(fn (FakturPajakDitarikDetail $model): array => [
                            'baris'              => $model->baris,
                            'jenis_barang_jasa'  => $model->jenis_barang_jasa,
                            'kode_barang_jasa'   => $model->kode_barang_jasa,
                            'nama_barang_jasa'   => $model->nama_barang_jasa,
                            'nama_satuan_ukur'   => $model->nama_satuan_ukur ?: 'UM.0033',
                            'harga_satuan'       => round($model->harga_satuan, 2),
                            'jumlah_barang_jasa' => round($model->jumlah_barang_jasa, 2),
                            'diskon_nominal'     => round($model->diskon_nominal, 2),
                            'dpp'                => round($model->dpp, 2),
                            'dpp_nilai_lain'     => round($model->dpp_nilai_lain, 2),
                            'ppn_persen'         => round($model->ppn_persen, 2),
                            'ppn_nominal'        => round($model->ppn_nominal, 2),
                            'ppnbm_persen'       => round($model->ppnbm_persen, 2),
                            'ppnbm_nominal'      => round($model->ppnbm_nominal, 2),
                        ]),
                ];
            default:
                return [
                    'Faktur' => fn () => FakturPajakDitarik::query()
                        ->where('menu', 'fp-asper')
                        ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
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
                            'alamat_asuransi'     => $model->alamat_asuransi,
                            'email_asuransi'      => $model->email_asuransi,
                            'npwp_asuransi'       => $model->npwp_asuransi,
                            'kode_perusahaan'     => $model->kode_perusahaan,
                            'nama_perusahaan'     => $model->nama_perusahaan,
                            'alamat_perusahaan'   => $model->alamat_perusahaan,
                            'email_perusahaan'    => $model->email_perusahaan,
                            'npwp_perusahaan'     => $model->npwp_perusahaan,
                        ]),
                    'Detail Faktur' => fn () => FakturPajakDitarikDetail::query()
                        ->where('menu', 'fp-asper')
                        ->whereBetween('tgl_tarikan', [$this->tanggalTarikan, $this->tanggalTarikan])
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
                            'ppnbm_nominal'      => 'float',
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
                            'harga_satuan'       => round($model->harga_satuan, 2),
                            'jumlah_barang_jasa' => round($model->jumlah_barang_jasa, 2),
                            'diskon_nominal'     => round($model->diskon_nominal, 2),
                            'dpp'                => round($model->dpp, 2),
                            'dpp_nilai_lain'     => round($model->dpp_nilai_lain, 2),
                            'ppn_persen'         => round($model->ppn_persen, 2),
                            'ppn_nominal'        => round($model->ppn_nominal, 2),
                            'ppnbm_persen'       => 0,
                            'ppnbm_nominal'      => 0,
                            'kd_jenis_prw'       => $model->kd_jenis_prw,
                            'kategori'           => $model->kategori,
                            'status_lanjut'      => $model->status_lanjut,
                            'kode_asuransi'      => $model->kode_asuransi,
                            'no_rkm_medis'       => $model->no_rkm_medis,
                        ]),
                ];
        }
    }

    protected function columnHeaders(): array
    {
        switch ($this->option) {
            case self::FORMAT_CORETAX:
                return [
                    'Faktur' => [
                        'Baris',
                        'Tgl. Faktur',
                        'Jenis Faktur',
                        'Kode Transaksi',
                        'Keterangan Tambahan',
                        'Dokumen Pendukung',
                        'Referensi',
                        'Cap Fasilitas',
                        'ID TKU Penjual',
                        'NPWP/NIK Pembeli',
                        'Jenis ID Pembeli',
                        'Negara Pembeli',
                        'Nomor Dokumen Pembeli',
                        'Nama Pembeli',
                        'Alamat Pembeli',
                        'Email Pembeli',
                        'ID TKU Pembeli',
                    ],
                    'Detail Faktur' => [
                        'Baris',
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
                    ],
                ];
            default:
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
                        'Alamat Asuransi',
                        'Email Asuransi',
                        'NPWP Asuransi',
                        'Kode Perusahaan',
                        'Nama Perusahaan',
                        'Alamat Perusahaan',
                        'Email Perusahaan',
                        'NPWP Perusahaan',
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
                        'Kode Asuransi',
                    ],
                ];
        }
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
            'Laporan Faktur Pajak ASURANSI / PERUSAHAAN',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
