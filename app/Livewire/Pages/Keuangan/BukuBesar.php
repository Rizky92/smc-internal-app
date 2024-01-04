<?php

namespace App\Livewire\Pages\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class BukuBesar extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $kodeRekening;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'kodeRekening' => ['except' => '', 'as' => 'rekening'],
            'tglAwal'      => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getBukuBesarProperty()
    {
        if ($this->isDeferred) {
            return [];
        }
    
        $jurnals = Jurnal::query()
            ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->with('pengeluaranHarian')
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
            ->paginate($this->perpage);
    
        return $jurnals;
    }
    
    public function getTotalDebetDanKreditProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
                ->jumlahDebetDanKreditBukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->search($this->cari)
                ->first();
    }

    public function getRekeningProperty(): array
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.buku-besar')
            ->layout(BaseLayout::class, ['title' => 'Jurnal Buku Besar']);
    }

    protected function dataPerSheet(): array
    {
        return [
            Jurnal::query()
                ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->with('pengeluaranHarian')
                ->search($this->cari)
                ->cursor()
                ->map(function (Jurnal $model) {
                    return [
                        'tgl_jurnal'             => $model->tgl_jurnal,
                        'jam_jurnal'             => $model->jam_jurnal,
                        'no_jurnal'              => $model->no_jurnal,
                        'no_bukti'               => $model->no_bukti,
                        'keterangan'             => $model->keterangan,
                        'keterangan_pengeluaran' => optional($model->pengeluaranHarian)->keterangan ?? '-',
                        'catatan_penagihan'      => $model->catatanPenagihan(),
                        'keterangan_medis'       => $model->keteranganMedis(),
                        'keterangan_non_medis'   => $model->keteranganNonMedis(),
                        'kd_rek'                 => $model->kd_rek,
                        'nm_rek'                 => $model->nm_rek,
                        'debet'                  => round($model->debet, 2),
                        'kredit'                 => round($model->kredit, 2),
                    ];
                })
                ->merge([[
                    'tgl_jurnal' => '',
                    'jam_jurnal' => '',
                    'no_jurnal'  => '',
                    'no_bukti'   => '',
                    'keterangan' => '',
                    'keterangan_pengeluaran' => '',
                    'catatan_penagihan' => '',
                    'keterangan_medis' => '',
                    'keterangan_non_medis' => '',
                    'kd_rek'     => '',
                    'nm_rek'     => 'TOTAL :',
                    'debet'      => round(optional($this->totalDebetDanKredit)->debet, 2),
                    'kredit'     => round(optional($this->totalDebetDanKredit)->kredit, 2),
                ]])
                ->all(),
        ];
    }


    protected function columnHeaders(): array
    {
        return [
            "Tgl",
            "Jam",
            "No. Jurnal",
            "No. Bukti",
            "Keterangan Jurnal",
            "Keterangan Pengeluaran",
            "Catatan Penagihan",
            "Keterangan Medis",
            "Keterangan Non Medis",
            "Kode",
            "Rekening",
            "Debet",
            "Kredit",
        ];
    }

    protected function pageHeaders(): array
    {
        $rekening = empty($this->kodeRekening) ? 'SEMUA' : $this->rekening[$this->kodeRekening];

        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Buku Besar rekening ' . $rekening,
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }

    protected function defaultValues(): void
    {
        $this->kodeRekening = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }
}
