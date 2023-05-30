<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class KunjunganPerBentukObat extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $jenisPerawatan;

    protected function queryString(): array
    {
        return [
            'jenisPerawatan' => ['except' => '', 'as' => 'jenis_perawatan'],
            'tglAwal'        => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.farmasi.kunjungan-per-bentuk-obat')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Obat']);
    }

    public function getDataKunjunganResepObatRegularProperty(): Paginator
    {
        return ResepDokter::query()
            ->kunjunganResepObatRegular($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
            ->search($this->cari, [
                'resep_dokter.no_resep',
                'dokter.nm_dokter',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ])
            ->sortWithColumns($this->sortColumns, ['total' => DB::raw('round(sum(resep_dokter.jml * databarang.h_beli))')])
            ->paginate($this->perpage, ['*'], 'page_regular');
    }

    public function getDataKunjunganResepObatRacikanProperty(): Paginator
    {
        return ResepDokterRacikan::query()
            ->kunjunganResepObatRacikan($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
            ->search($this->cari, [
                'resep_dokter_racikan.no_resep',
                'dokter.nm_dokter',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ])
            ->sortWithColumns($this->sortColumns, ['total' => DB::raw('round(sum(resep_dokter_racikan_detail.jml * databarang.h_beli))')])
            ->paginate($this->perpage, ['*'], 'page_racikan');
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';
    }

    public function searchData(): void
    {
        $this->resetPage('page_regular');
        $this->resetPage('page_racikan');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Obat Regular' => ResepDokter::query()
                ->kunjunganResepObatRegular($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
                ->get()
                ->map(fn (ResepDokter $model): array => [
                    'no_resep'      => $model->no_resep,
                    'nm_dokter'     => $model->nm_dokter,
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam'           => $model->jam,
                    'nm_pasien'     => $model->nm_pasien,
                    'nm_poli'       => $model->nm_poli,
                    'status_lanjut' => $model->status_lanjut,
                    'total'         => floatval($model->total),
                ]),

            'Obat Racikan' => ResepDokterRacikan::query()
                ->kunjunganResepObatRacikan($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
                ->get()
                ->map(fn (ResepDokterRacikan $model): array => [
                    'no_resep'      => $model->no_resep,
                    'nm_dokter'     => $model->nm_dokter,
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam'           => $model->jam,
                    'nm_pasien'     => $model->nm_pasien,
                    'nm_poli'       => $model->nm_poli,
                    'status_lanjut' => $model->status_lanjut,
                    'total'         => floatval($model->total),
                ]),

        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Resep',
            'Dokter Peresep',
            'Tgl. Validasi',
            'Jam',
            'Pasien',
            'Asal Poli',
            'Jenis Perawatan',
            'Total Pembelian (RP)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Resep Farmasi per Bentuk Obat',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
