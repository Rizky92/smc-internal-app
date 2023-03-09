<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class ObatPerDokter extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString(): array
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

    public function getObatPerDokterProperty()
    {
        return ResepObat::query()
            ->penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir)
            ->search($this->cari, [
                'resep_obat.no_resep',
                'databarang.nama_brng',
                'dokter.nm_dokter',
                'resep_obat.status',
                'poliklinik.nm_poli',
            ])
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.obat-per-dokter')
            ->layout(BaseLayout::class, ['title' => 'Penggunaan Obat Per Dokter Peresep']);
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
            ResepObat::penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir)->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Resep',
            'Tgl. Validasi',
            'Jam',
            'Nama Obat',
            'Jumlah',
            'Dokter Peresep',
            'Asal',
            'Asal Poli',
        ];
    }

    protected function pageHeaders(): array
    {
        $headerTglAwal = carbon($this->periodeAwal)->format('d F Y');
        $headerTglAkhir = carbon($this->periodeAkhir)->format('d F Y');

        return [
            'RS Samarinda Medika Citra',
            'Laporan Penggunaan Obat Per Dokter Peresep',
            "{$headerTglAwal} - {$headerTglAkhir}",
        ];
    }
}
