<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganPerBentukObat extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    private const OBAT_REGULAR_PAGE = 'obat_regular_page';
    private const OBAT_RACIKAN_PAGE = 'obat_racikan_page';

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    public $jenisPerawatan;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'jenisPerawatan' => ['except' => '', 'as' => 'jenis_perawatan'],
            'perpage' => ['except' => 25],
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'periode_akhir'],
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
        return ResepDokter::kunjunganResepObatRegular(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], self::OBAT_REGULAR_PAGE);
    }

    public function getKunjunganResepObatRacikanPasienProperty()
    {
        return ResepDokterRacikan::kunjunganResepObatRacikan(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], self::OBAT_RACIKAN_PAGE);
    }

    protected function defaultValues()
    {
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';        
    }

    public function searchData()
    {
        $this->resetPage(self::OBAT_REGULAR_PAGE);
        $this->resetPage(self::OBAT_RACIKAN_PAGE);

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Obat Regular' => ResepDokter::kunjunganResepObatRegular($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get(),
            'Obat Racikan' => ResepDokterRacikan::kunjunganResepObatRacikan($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get(),
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
