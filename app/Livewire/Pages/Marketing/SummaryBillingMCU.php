<?php

namespace App\Livewire\Pages\Marketing;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class SummaryBillingMCU extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

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
     * @return array<empty,empty>|\Illuminate\Pagination\Paginator
     */
    public function getDataSummaryBillingMCUProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->summaryBillingMcu($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.marketing.summary-billing-m-c-u')
            ->layout(BaseLayout::class, ['title' => 'Laporan Summary Billing MCU']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->summaryBillingMCU($this->tglAwal, $this->tglAkhir)
                ->orderBy('reg_periksa.no_rawat')
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'No. Rawat'        => $model->no_rawat,
                    'No. RM'           => $model->no_rkm_medis,
                    'Nama Pasien'      => $model->nm_pasien,
                    'Tgl. Registrasi'  => $model->tgl_registrasi,
                    'Jenis Bayar'      => $model->png_jawab,
                    'Penanggung Jawab' => $model->p_jawab,
                    'Nama Dokter'      => $model->nm_dokter,
                    'Total Billing'    => $model->total_billing,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'Tgl. Registrasi',
            'Jenis Bayar',
            'Penanggung Jawab',
            'Nama Dokter',
            'Total Billing',
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
            'Laporan Summary Billing MCU',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
