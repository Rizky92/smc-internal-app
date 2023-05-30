<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
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
                ->search($this->cari, [
                    'jurnal.no_jurnal',
                    'jurnal.no_bukti',
                    'jurnal.keterangan',
                ])
                ->sortWithColumns($this->sortColumns, [
                    'waktu_jurnal' => DB::raw('timestamp(jurnal.tgl_jurnal, jurnal.jam_jurnal)'),
                ])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.jurnal-perbaikan')
            ->layout(BaseLayout::class, ['title' => 'Perbaikan Tanggal Jurnal Transaksi Keuangan']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }
}
