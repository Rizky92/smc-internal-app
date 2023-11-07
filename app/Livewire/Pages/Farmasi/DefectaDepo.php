<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class DefectaDepo extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tanggal;

    /** @var "-"|"Pagi"|"Siang"|"Malam" */
    public $shift;

    /** @var "-"|"IFA"|"IFG"|"IFI" */
    public $bangsal;

    protected function queryString(): array
    {
        return [
            'tanggal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'shift'   => ['except' => '-', 'as' => 'shift_kerja'],
            'bangsal' => ['except' => '-', 'as' => 'depo'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataDefectaDepoProperty()
    {
        return $this->isDeferred
            ? []
            : GudangObat::query()
                ->defectaDepo($this->tanggal, $this->shift, $this->bangsal)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.defecta-depo')
            ->layout(BaseLayout::class, ['title' => 'DefectaDepo']);
    }

    protected function defaultValues(): void
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->bangsal = '-';
        $this->shift = '-';
    }

    protected function dataPerSheet(): array
    {
        return [
            GudangObat::query()
                ->defectaDepo($this->tanggal, $this->shift, $this->bangsal)
                ->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Satuan',
            'Stok Sekarang',
            'Jumlah Pemakaian per Shift',
            'Jumlah Pemakaian 3 Hari Terakhir',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
