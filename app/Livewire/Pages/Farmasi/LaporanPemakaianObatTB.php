<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatTB extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var "anak"|"dewasa" */
    public $umur;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'umur'     => ['except' => '', 'as' => 'umur'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanPemakaianObatTBProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->riwayatPemakaianObatTB($this->tglAwal, $this->tglAkhir)
            ->when($this->umur === 'anak', fn ($q) => $q->where(function ($query) {
                $query->where('umurdaftar', '<', 18)
                    ->where(function ($q) {
                        $q->where('sttsumur', 'Th')
                            ->orWhere('sttsumur', 'Bl')
                            ->orWhere('sttsumur', 'Hr');
                    });
            }))
            ->when($this->umur === 'dewasa', fn ($q) => $q->where('umurdaftar', '>=', 18)->where('sttsumur', 'Th'))
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-tb')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat TB per Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->umur = 'anak';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->riwayatPemakaianObatTB($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor(),
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
            'Umur',
            'Status umur',
            'No. Telp',
            'Alamat',
            'Plan',
            'Tanggal Pemberian Obat Pertama',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

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
