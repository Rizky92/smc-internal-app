<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\ResepObat;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class RincianKunjunganRalan extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $totalHarga;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'totalHarga' => ['except' => '', 'as' => 'total_harga'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getRincianKunjunganRalanProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->with('pemberian', 'pemberian.obat')
            ->rincianKunjunganRalan($this->tglAwal, $this->tglAkhir)
            ->when($this->totalHarga === 'below_100k', fn (Builder $q): Builder => $q->having('total_harga', '<', 100000))
            ->when($this->totalHarga === 'above_100k', fn (Builder $q): Builder => $q->having('total_harga', '>=', 100000))
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }
    

    public function render(): View
    {
        return view('livewire.pages.farmasi.rincian-kunjungan-ralan')
            ->layout(BaseLayout::class, ['title' => 'Rincian Kunjungan Ralan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->totalHarga = 'below_100k';
    }

    protected function dataPerSheet(): array
    {
        return [
            ResepObat::query()
                ->with(['pemberian', 'pemberian.obat'])
                ->rincianKunjunganRalan($this->tglAwal, $this->tglAkhir, $this->totalHarga)
                ->when($this->totalHarga === 'below_100k', fn (Builder $q): Builder => $q->having('total_harga', '<', 100000))
                ->when($this->totalHarga === 'above_100k', fn (Builder $q): Builder => $q->having('total_harga', '>=', 100000))
                ->search($this->cari)
                ->cursor(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tanggal',
            'No. Rawat',
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
            'Rincian Kunjungan Ralan ',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
