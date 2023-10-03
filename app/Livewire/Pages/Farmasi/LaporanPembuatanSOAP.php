<?php

namespace App\Livewire\Pages\Farmasi;

use App\Models\Perawatan\PemeriksaanRanap;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPembuatanSOAP extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var ?string */
    public $tglAwal;

    /** @var ?string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataLaporanPembuatanSOAPProperty()
    {
        return $this->isDeferred
            ? []
            : PemeriksaanRanap::query()
            ->pemeriksaanOlehFarmasi($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'pemeriksaan_ranap.no_rawat',
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab.kd_pj',
                'penjab.png_jawab',
                'ifnull(pemeriksaan_ranap.alergi, "")',
                'ifnull(pemeriksaan_ranap.keluhan, "")',
                'ifnull(pemeriksaan_ranap.pemeriksaan, "")',
                'ifnull(pemeriksaan_ranap.penilaian, "")',
                'ifnull(pemeriksaan_ranap.rtl, "")',
                'ifnull(pemeriksaan_ranap.instruksi, "")',
                'ifnull(pemeriksaan_ranap.evaluasi, "")',
                'pemeriksaan_ranap.nip',
                'petugas.nama',
                'jabatan.nm_jbtn',
            ])
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pembuatan-soap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pembuatan SOAP Farmasi/Visite Apoteker']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            PemeriksaanRanap::query()
                ->pemeriksaanOlehFarmasi($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (PemeriksaanRanap $model): array => [
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam_rawat'     => $model->jam_rawat,
                    'no_rawat'      => $model->no_rawat,
                    'nm_pasien'     => $model->nm_pasien,
                    'png_jawab'     => $model->png_jawab,
                    'dpjp'          => optional($model->dpjp)->pluck('nm_dokter')->join('; '),
                    'alergi'        => $model->alergi,
                    'keluhan'       => $model->keluhan,
                    'pemeriksaan'   => $model->pemeriksaan,
                    'penilaian'     => $model->penilaian,
                    'rtl'           => $model->rtl,
                    'nip'           => $model->nip . ' ' . $model->nama,
                    'nm_jbtn'       => $model->nm_jbtn,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl. SOAP',
            'Jam',
            'No. Rawat',
            'Pasien',
            'Jenis Bayar',
            'DPJP',
            'Alergi',
            'Keluhan (Subjek)',
            'Pemeriksaan (Objek)',
            'Penilaian (Asesmen)',
            'RTL (Plan)',
            'Petugas',
            'Jabatan',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Pembuatan SOAP oleh Farmasi',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
