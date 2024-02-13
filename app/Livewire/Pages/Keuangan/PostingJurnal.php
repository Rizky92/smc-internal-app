<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\PostingJurnal as ModelPostingJurnal;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class PostingJurnal extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var "U"|"P" */
    public $jenis;
    
    protected function queryString(): array
    {
        return [
            'jenis'    => ['as' => 'jenis'],
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPostingJurnalProperty()
    {
        return $this->isDeferred
        ? [] 
        : ModelPostingJurnal::query()
            ->postingJurnal($this->tglAwal, $this->tglAkhir, $this->jenis)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'tgl_jurnal' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function getTotalDebetDanKreditProperty()
    {
        return $this->isDeferred
            ? []
            : ModelPostingJurnal::query()
                ->jumlahDebetDanKreditPostingJurnal($this->tglAwal, $this->tglAkhir, $this->jenis)
                ->search($this->cari)
                ->first();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.posting-jurnal')
            ->layout(BaseLayout::class, ['title' => 'Posting Jurnal']);
    }

    public function prepareAndPrint()
    {
        $data = [
            'dataPostingJurnal' => $this->dataPostingJurnal,
            'totalDebetDanKredit' => optional($this->totalDebetDanKredit),
        ];

        return Redirect::route('print-layout', ['no_jurnal' => $this->dataPostingJurnal[0]->no_jurnal])->with($data);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenis = 'U';
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
