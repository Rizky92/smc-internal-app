<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class BukuBesar extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $kodeRekening;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'kodeRekening' => ['except' => '', 'as' => 'rekening'],
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getBukuBesarProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
            ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->search($this->cari, [
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'rekening.nm_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit',
            ])
            ->sortWithColumns($this->sortColumns, [], [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function getTotalDebetDanKreditProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
                ->jumlahDebetDanKreditBukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->first();
    }

    public function getRekeningProperty()
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function render()
    {
        return view('livewire.keuangan.buku-besar')
            ->layout(BaseLayout::class, ['title' => 'Jurnal Buku Besar']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->kodeRekening = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            Jurnal::query()
                ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->cursor()
                ->map(function ($data) {
                    return [
                        'tgl_jurnal' => $data->tgl_jurnal,
                        'jam_jurnal' => $data->jam_jurnal,
                        'no_jurnal' => $data->no_jurnal,
                        'no_bukti' => $data->no_bukti,
                        'keterangan' => $data->keterangan,
                        'kd_rek' => $data->kd_rek,
                        'nm_rek' => $data->nm_rek,
                        'debet' => round($data->debet, 2),
                        'kredit' => round($data->kredit, 2),
                    ];
                })
                ->merge([
                    [
                        'tgl_jurnal' => '',
                        'jam_jurnal' => '',
                        'no_jurnal' => '',
                        'no_bukti' => '',
                        'keterangan' => '',
                        'kd_rek' => '',
                        'nm_rek' => 'TOTAL :',
                        'debet' => round(optional($this->totalDebetDanKredit)->debet, 2),
                        'kredit' => round(optional($this->totalDebetDanKredit)->kredit, 2),
                    ]
                ]),
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
            'Kode',
            'Rekening',
            'Debet',
            'Kredit',
        ];
    }

    protected function pageHeaders(): array
    {
        $rekening = empty($this->kodeRekening) ? 'SEMUA' : $this->rekening[$this->kodeRekening];
        return [
            'RS Samarinda Medika Citra',
            'Buku Besar rekening ' . $rekening,
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
