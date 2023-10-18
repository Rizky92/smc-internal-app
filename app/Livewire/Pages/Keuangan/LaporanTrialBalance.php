<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Rekening;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Fluent;
use Illuminate\View\View;
use Livewire\Component;
use Illuminate\Support\Str;

class LaporanTrialBalance extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<never, never>|\Illuminate\Database\Eloquent\Collection<\App\Models\Keuangan\Jurnal\Jurnal>
     */
    public function getDataTrialBalancePerTanggalProperty()
    {
        if ($this->isDeferred) return [];

        $bulan = carbon($this->tglAwal)->subMonth()->format('Y_m');

        $semuaRekening = Cache::remember(
            'semua_rekening', now()->addMonth(), fn (): EloquentCollection => Rekening::query()
                ->semuaRekening()
                ->get()
        );

        $saldoBulanSebelumnya = Cache::remember(
            'saldo_' . $bulan, now()->addWeek(), fn (): Collection => Rekening::query()
                ->saldoAwalBulanSebelumnya($this->tglAwal)
                ->pluck('saldo_awal', 'kd_rek')
        );

        $trialBalance = Rekening::query()
            ->trialBalancePerTanggal($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->get()
            ->map(function (Rekening $rekening) use ($saldoBulanSebelumnya) {
                $rekening->setAttribute('saldo_awal', $saldoBulanSebelumnya->get($rekening->kd_rek));

                $saldoAkhir = 0;

                if ($rekening->balance === "D") {
                    $saldoAkhir = $rekening->saldo_awal + $rekening->total_debet - $rekening->total_kredit;
                } else {
                    $saldoAkhir = $rekening->saldo_awal + $rekening->total_kredit - $rekening->total_debet;
                }

                $rekening->setAttribute('saldo_akhir', $saldoAkhir);

                return $rekening;
            });

        return $semuaRekening
            ->map(function (Rekening $rekening) use ($saldoBulanSebelumnya, $trialBalance) {
                $rekening->nm_rek = Str::transliterate($rekening->nm_rek);

                $rekening->setAttribute('saldo_awal', $saldoBulanSebelumnya->get($rekening->kd_rek) ?? 0);

                $rekening->setAttribute('total_debet', optional($trialBalance->find($rekening->kd_rek))->total_debet ?? 0);

                $rekening->setAttribute('total_kredit', optional($trialBalance->find($rekening->kd_rek))->total_kredit ?? 0);

                $saldoAkhir = 0;

                if ($rekening->balance === 'D') {
                    $saldoAkhir = $rekening->saldo_awal + $rekening->total_debet - $rekening->total_kredit;
                } else {
                    $saldoAkhir = $rekening->saldo_awal + $rekening->total_kredit - $rekening->total_debet;
                }

                $rekening->setAttribute('saldo_akhir', $saldoAkhir);

                return $rekening;
            });
    }

    /**
     * @return array
     */
    public function getTotalDebetKreditTrialBalanceProperty()
    {
        if ($this->isDeferred) return [];

        $totalDebet = $this->dataTrialBalancePerTanggal->sum('total_debet');

        $totalKredit = $this->dataTrialBalancePerTanggal->sum('total_kredit');

        return new Fluent([
            'kd_rek' => null,
            'nm_rek' => 'TOTAL',
            'tipe' => null,
            'saldo_awal' => null,
            'total_debet' => $totalDebet,
            'total_kredit' => $totalKredit,
            'saldo_akhir' => null,
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-trial-balance')
            ->layout(BaseLayout::class, ['title' => 'Laporan Trial Balance Per Bulan']);
    }

    public function resetCache(): void
    {
        $bulan = carbon($this->tglAwal)->subMonth()->format('Y_m');

        Cache::forget('saldo_' . $bulan);

        $this->searchData();

        $this->flashSuccess("Rekening berhasil direkalkulasi ulang!");
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            $this
                ->dataTrialBalancePerTanggal
                ->push($this->totalDebetKreditTrialBalance)
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Tipe Balance',
            'Saldo Awal',
            'Debet',
            'Kredit',
            'Saldo Akhir',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Trial Balance Rekening',
            $periode,
            null,
            'Saldo Awal per ' . $periodeAwal->startOfMonth()->format('Y-m-d'),
        ];
    }
}
