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
use Livewire\Component;

class JurnalPerbaikan extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

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

    public function render()
    {
        return view('livewire.keuangan.jurnal-perbaikan')
            ->layout(BaseLayout::class, ['title' => 'Perbaikan Tanggal Jurnal Transaksi Keuangan']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }
}
