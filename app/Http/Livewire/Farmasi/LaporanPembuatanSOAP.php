<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Perawatan\PemeriksaanRanap;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
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
                    'kamar_inap.kd_kamar',
                    'bangsal.kd_bangsal',
                    'bangsal.nm_bangsal',
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
        return view('livewire.farmasi.laporan-pembuatan-soap')
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
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
