<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Rekening;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Livewire\Component;
use Livewire\WithPagination;

class LabaRugiRekeningPerPeriode extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getLabaRugiPerRekeningProperty()
    {
        if ($this->isDeferred) {
            return collect(['D' => [], 'K' => []]);
        }

        $debetKredit = Rekening::query()
            ->hitungDebetKreditPerPeriode($this->periodeAwal, $this->periodeAkhir)
            ->get();

        return $debetKredit->map(function (Rekening $rekening) {
            $total = 0;

            $debet = $rekening->debet ?? 0;
            $kredit = $rekening->kredit ?? 0;

            if ($rekening->balance === 'K') {
                $total = $kredit - $debet;
            }

            if ($rekening->balance === 'D') {
                $total = $debet - $kredit;
            }

            return new Fluent(array_merge(
                $rekening->only('kd_rek', 'nm_rek', 'balance'),
                ['debet' => $debet],
                ['kredit' => $kredit],
                ['total' => $total],
            ));
        })
            ->mapToGroups(fn ($item) => [$item->balance => $item]);
    }

    public function getTotalLabaRugiPerRekeningProperty()
    {
        $pendapatan = collect($this->labaRugiPerRekening->get('K'));

        $bebanDanBiaya = collect($this->labaRugiPerRekening->get('D'));

        $totalDebetPendapatan = $pendapatan->sum('debet');
        $totalKreditPendapatan = $pendapatan->sum('kredit');
        $totalPendapatan = $totalKreditPendapatan - $totalDebetPendapatan;

        $totalDebetBeban = $bebanDanBiaya->sum('debet');
        $totalKreditBeban = $bebanDanBiaya->sum('kredit');
        $totalBebanDanBiaya = $totalDebetBeban - $totalKreditBeban;

        $labaRugi = $totalPendapatan - $totalBebanDanBiaya;

        return compact(
            'totalPendapatan',
            'totalDebetPendapatan',
            'totalKreditPendapatan',
            'totalBebanDanBiaya',
            'totalDebetBeban',
            'totalKreditBeban',
            'labaRugi'
        );
    }

    public function render()
    {
        return view('livewire.keuangan.laba-rugi-rekening-per-periode')
            ->layout(BaseLayout::class, ['title' => 'Laporan Laba Rugi']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function mapDataForExcelExport()
    {
        $pendapatanRowHeader = $this->insertExcelRow('', 'PENDAPATAN');
        $bebanRowHeader = $this->insertExcelRow('', 'BEBAN & BIAYA');
        $empty = $this->insertExcelRow();

        $pendapatan = $this->labaRugiPerRekening->get('K');
        $beban = $this->labaRugiPerRekening->get('D');

        $total = $this->totalLabaRugiPerRekening;

        $totalPendapatanRow = $this->insertExcelRow('', 'TOTAL', '', $total['totalDebetPendapatan'], $total['totalKreditPendapatan'], $total['totalPendapatan']);
        $totalBebanRow = $this->insertExcelRow('', 'TOTAL', '', $total['totalDebetBeban'], $total['totalKreditBeban'], $total['totalBebanDanBiaya']);

        $pendapatanBersih = $this->insertExcelRow('', 'PENDAPATAN BERSIH', '', $total['totalPendapatan'], $total['totalBebanDanBiaya'], $total['labaRugi']);

        return collect([$pendapatanRowHeader])
            ->merge($pendapatan)
            ->merge([$totalPendapatanRow, $empty])
            ->merge([$bebanRowHeader])
            ->merge($beban)
            ->merge([$totalBebanRow, $empty])
            ->merge([$pendapatanBersih]);
    }

    protected function insertExcelRow($kd_rek = '', $nm_rek = '', $balance = '', $debet = '', $kredit = '', $total = '')
    {
        return new Fluent(func_get_named_args($this, 'insertExcelRow', func_get_args()));
    }

    protected function dataPerSheet(): array
    {
        return [
            $this->mapDataForExcelExport(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Jenis',
            'Debet',
            'Kredit',
            'Total',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Laba Rugi Keuangan',
            now()->format('d F Y'),
            'Periode ' . CarbonImmutable::parse($this->periodeAwal)->format('d F Y') . ' - ' . CarbonImmutable::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
