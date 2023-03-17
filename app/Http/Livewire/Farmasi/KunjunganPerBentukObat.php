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
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganPerBentukObat extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $tglAwal;

    public $tglAkhir;

    public $jenisPerawatan;

    protected function queryString()
    {
        return [
            'jenisPerawatan' => ['except' => '', 'as' => 'jenis_perawatan'],
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-per-bentuk-obat')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Obat']);
    }

    public function getKunjunganResepObatRegularPasienProperty()
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

    public function getKunjunganResepObatRacikanPasienProperty()
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

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';        
    }

    public function searchData()
    {
        $this->resetPage('page_regular');
        $this->resetPage('page_racikan');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Obat Regular' => ResepDokter::kunjunganResepObatRegular($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)->get(),
            'Obat Racikan' => ResepDokterRacikan::kunjunganResepObatRacikan($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)->get(),
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
            'Jenis Perawatan',
            'Total Pembelian (RP)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Resep Farmasi per Bentuk Obat',
            now()->format('d F Y'),
        ];
    }
}
