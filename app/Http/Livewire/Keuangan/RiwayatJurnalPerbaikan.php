<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\JurnalBackup;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class RiwayatJurnalPerbaikan extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataRiwayatJurnalPerbaikanProperty()
    {
        return JurnalBackup::query()
            ->with('pegawai', 'jurnal')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.riwayat-jurnal-perbaikan')
            ->layout(BaseLayout::class, ['title' => 'Riwayat Jurnal Perbaikan']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
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
