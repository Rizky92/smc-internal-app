<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class BukuBesar extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public $kodeRekening;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'kodeRekening' => ['except' => ''],
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBukuBesarProperty()
    {
        if (empty($this->kodeRekening)) {
            return collect();
        }

        return Jurnal::query()
            ->bukuBesar($this->kodeRekening, $this->periodeAwal, $this->periodeAkhir)
            ->search($this->cari, [
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit',
            ])
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    /**
     * @return \App\Models\Keuangan\Jurnal\Jurnal|int
     */
    public function getTotalDebetDanKreditProperty()
    {
        if (empty($this->kodeRekening)) {
            return null;
        }

        return Jurnal::query()
            ->jumlahDebetDanKreditBukuBesar($this->kodeRekening, $this->periodeAwal, $this->periodeAkhir)
            ->first();
    }

    public function getRekeningProperty()
    {
        return Rekening::pluck('nm_rek', 'kd_rek');
    }

    public function render()
    {
        return view('livewire.keuangan.buku-besar')
            ->layout(BaseLayout::class, ['title' => 'Jurnal Buku Besar']);
    }

    protected function defaultValues()
    {
        $this->kodeRekening = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
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
