<?php

namespace App\Livewire\Pages\Farmasi;

use App\Models\Perawatan\RegistrasiPasien;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatTB extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    public function getDataLaporanPemakaianObatTBProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->riwayatPemakaianObatTB($this->tglAwal, $this->tglAkhir, $this->cari)
            ->sortWithColumns($this->sortColumns, [
                'total' => DB::raw('sum(detail_pemberian_obat.jml)')
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-tb')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat TB per Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->riwayatPemakaianObatTB($this->tglAwal, $this->tglAkhir, $this->cari)
                ->cursor()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Registrasi',
            'No. RM',
            'Pasien',
            'NIK',
            'Obat Diberikan',
            'Jumlah',
            'Farmasi',
            'Status',
            'Penjamin',
            'No. Telp',
            'Alamat',
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
            'Laporan Pemakaian Obat TB ke Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}