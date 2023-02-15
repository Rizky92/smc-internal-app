<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BukuBesar extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

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
            ->sortWithColumns($this->sortColumns, [], [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
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
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek');
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
        $bukuBesar = Jurnal::bukuBesar($this->kodeRekening, $this->periodeAwal, $this->periodeAkhir)->get();

        return [
            collect($bukuBesar->toArray())
                ->merge([
                    [
                        'tgl_jurnal' => '',
                        'jam_jurnal' => '',
                        'no_jurnal' => '',
                        'no_bukti' => '',
                        'keterangan' => '',
                        'kd_rek' => 'TOTAL :',
                        'debet' => optional($this->totalDebetDanKredit)->debet,
                        'kredit' => optional($this->totalDebetDanKredit)->kredit,
                    ]
                ])
                ->toArray(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl.',
            'Jam',
            'No. Jurnal',
            'No. Bukti',
            'Keterangan',
            'Rekening',
            'Debet',
            'Kredit',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Buku Besar rekening' . $this->kodeRekening,
            now()->format('d F Y'),
            'Periode ' . Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
