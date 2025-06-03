<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Models\Keuangan\RekeningTahun;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTrialBalance extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<never, never>|EloquentCollection<Jurnal>
     */
    public function getDataTrialBalancePerTanggalProperty()
    {
        if ($this->isDeferred) {
            return [];
        }

        $bulan = carbon($this->tglAwal)->subMonth()->format('Y_m');

        $semuaRekening = Cache::remember(
            'semua_rekening',
            now()->addMonth(),
            fn (): EloquentCollection => Rekening::query()
                ->semuaRekening()
                ->get()
        );

        $rekeningPerTahun = Cache::remember(
            'rekening_tahun',
            now()->addMonth(),
            fn (): Collection => RekeningTahun::query()
                ->where('thn', carbon($this->tglAwal)->year)
                ->pluck('saldo_awal', 'kd_rek')
        );

        $saldoBulanSebelumnya = Cache::remember(
            'saldo_'.$bulan,
            now()->addWeek(),
            fn (): Collection => Rekening::query()
                ->saldoAwalBulanSebelumnya($this->tglAwal)
                ->pluck('total_transaksi', 'kd_rek')
        );

        $trialBalance = Rekening::query()
            ->trialBalancePerTanggal($this->tglAwal, $this->tglAkhir)
            ->get();

        return $semuaRekening
            ->map(function (Rekening $rekening) use ($rekeningPerTahun, $saldoBulanSebelumnya, $trialBalance) {
                $rekening->nm_rek = Str::transliterate($rekening->nm_rek);

                $saldoAwal = $rekeningPerTahun->get($rekening->kd_rek) + $saldoBulanSebelumnya->get($rekening->kd_rek);
                $saldoAkhir = 0;

                $rekening->setAttribute('saldo_awal', $saldoAwal);

                $rekening->setAttribute('total_debet', optional($trialBalance->find($rekening->kd_rek))->total_debet ?? 0);

                $rekening->setAttribute('total_kredit', optional($trialBalance->find($rekening->kd_rek))->total_kredit ?? 0);

                if ($rekening->balance === 'D') {
                    $saldoAkhir = $saldoAwal + $rekening->total_debet - $rekening->total_kredit;
                } else {
                    $saldoAkhir = $saldoAwal + $rekening->total_kredit - $rekening->total_debet;
                }

                $rekening->setAttribute('saldo_akhir', $saldoAkhir);

                return $rekening;
            });
    }

    /**
     * @return Fluent|array<never, never>
     */
    public function getTotalDebetKreditTrialBalanceProperty()
    {
        if ($this->isDeferred) {
            return [];
        }

        $totalDebet = $this->dataTrialBalancePerTanggal->sum('total_debet');

        $totalKredit = $this->dataTrialBalancePerTanggal->sum('total_kredit');

        return new Fluent([
            'kd_rek'       => null,
            'nm_rek'       => 'TOTAL',
            'tipe'         => null,
            'saldo_awal'   => null,
            'total_debet'  => $totalDebet,
            'total_kredit' => $totalKredit,
            'saldo_akhir'  => null,
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

        Cache::forget('saldo_'.$bulan);

        Cache::forget('rekening_tahun');

        $this->searchData();

        $this->flashSuccess('Rekening berhasil direkalkulasi ulang!');
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            $this->dataTrialBalancePerTanggal->push($this->totalDebetKreditTrialBalance),
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Trial Balance Rekening',
            $periode,
            null,
            'Saldo Awal per '.$periodeAwal->startOfMonth()->format('d F Y'),
        ];
    }
}
