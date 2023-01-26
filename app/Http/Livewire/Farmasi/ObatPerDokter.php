<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class ObatPerDokter extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected function queryString(): array
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
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
            ->penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir, Str::lower($this->cari))
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
        $headerTglAwal = Carbon::parse($this->periodeAwal)->format('d F Y');
        $headerTglAkhir = Carbon::parse($this->periodeAkhir)->format('d F Y');

        return [
            'RS Samarinda Medika Citra',
            'Laporan Penggunaan Obat Per Dokter Peresep',
            "{$headerTglAwal} - {$headerTglAkhir}",
        ];
    }
}
