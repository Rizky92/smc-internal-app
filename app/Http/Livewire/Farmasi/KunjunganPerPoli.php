<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganPerPoli extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'periode_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataKunjunganResepPasienProperty()
    {
        return ResepObat::query()
            ->kunjunganFarmasi($this->periodeAwal, $this->periodeAkhir)
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

    public function render()
    {
        return view('livewire.farmasi.kunjungan-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Poli']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            ResepObat::kunjunganFarmasi($this->periodeAwal, $this->periodeAkhir)->get()
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
            now()->format('d F Y'),
        ];
    }
}
