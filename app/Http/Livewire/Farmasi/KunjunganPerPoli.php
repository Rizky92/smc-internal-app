<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
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
class KunjunganPerPoli extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataKunjunganResepPasienProperty(): Paginator
    {
        return ResepObat::query()
            ->kunjunganPerPoli($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'resep_obat.no_rawat',
                'resep_obat.no_resep',
                'pasien.nm_pasien',
                'dokter_peresep.nm_dokter',
                'dokter_poli.nm_dokter',
                'reg_periksa.status_lanjut',
                'poliklinik.nm_poli',
            ])
            ->sortWithColumns($this->sortColumns, [
                'umur' => DB::raw("concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur)"),
                'nm_dokter_peresep' => 'dokter_peresep.nm_dokter',
                'nm_dokter_poli' => 'dokter_poli.nm_dokter',
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.farmasi.kunjungan-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Poli']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            ResepObat::query()
                ->kunjunganPerPoli($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (ResepObat $model): array => [
                    'no_rawat'          => $model->no_rawat,
                    'no_resep'          => $model->no_resep,
                    'nm_pasien'         => $model->nm_pasien,
                    'umur'              => $model->umur,
                    'tgl_perawatan'     => $model->tgl_perawatan,
                    'jam'               => $model->jam,
                    'nm_dokter_peresep' => $model->nm_dokter_peresep,
                    'nm_dokter_poli'    => $model->nm_dokter_poli,
                    'status_lanjut'     => $model->status_lanjut,
                    'nm_poli'           => $model->nm_poli,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. Resep',
            'Pasien',
            'Umur',
            'Tgl. Validasi',
            'Jam',
            'Dokter Peresep',
            'Dokter Poli',
            'Jenis Perawatan',
            'Asal Poli',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Pasien Per Poli di Farmasi',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
