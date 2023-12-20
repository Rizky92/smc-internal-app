<?php

namespace App\Livewire\Pages\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class JurnalPerbaikan extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getJurnalProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
                ->jurnalUmum($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.jurnal-perbaikan')
            ->layout(BaseLayout::class, ['title' => 'Perbaikan Tanggal Jurnal Transaksi Keuangan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }
}
