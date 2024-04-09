<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class DaftarRiwayatObatAlkes extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tanggal;

    protected function queryString(): array
    {
        return [
            'tanggal' => ['except' => now()->format('Y-m-d'), 'as' => 'tanggal'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    
    public function render(): View
    {
        return view('livewire.pages.farmasi.daftar-riwayat-obat-alkes')
            ->layout(BaseLayout::class, ['title' => 'Daftar Riwayat Obat/Alkes']);
    }

    public function getDataRiwayatObatProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('obat',$this->tanggal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page-obat');
    }

    public function getDataRiwayatAlkesProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('alkes',$this->tanggal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page-alkes');
    }

    protected function defaultValues(): void
    {
        $this->tanggal = now()->format('Y-m-d');
    }

    public function searchData(): void
    {
        $this->resetPage('page-obat');
        $this->resetPage('page-alkes');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
