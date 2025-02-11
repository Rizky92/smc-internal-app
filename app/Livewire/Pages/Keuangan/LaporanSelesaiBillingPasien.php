<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\NotaSelesai;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanSelesaiBillingPasien extends Component
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
     * @return Paginator|array
     */
    public function getBillingYangDiselesaikanProperty()
    {
        return $this->isDeferred
            ? []
            : NotaSelesai::query()
                ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-selesai-billing-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Penyelesaian Billing Pasien per Petugas']);
    }

    public function tarikDataTerbaru(): void
    {
        NotaSelesai::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
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
            fn () => NotaSelesai::query()
                ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (NotaSelesai $model) => [
                    'no_rawat'         => $model->no_rawat,
                    'no_rkm_medis'     => $model->no_rkm_medis,
                    'nm_pasien'        => $model->nm_pasien,
                    'no_nota'          => $model->no_nota,
                    'ruangan'          => $model->ruangan,
                    'status_pasien'    => $model->status_pasien,
                    'bentuk_bayar'     => $model->bentuk_bayar,
                    'besar_bayar'      => floatval($model->besar_bayar),
                    'png_jawab'        => $model->png_jawab,
                    'tgl_penyelesaian' => $model->tgl_penyelesaian,
                    'nama_pegawai'     => $model->nama_pegawai,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'No. Nota',
            'Ruang Inap',
            'Jenis Perawatan',
            'Bentuk Pembayaran',
            'Nominal Yang Dibayarkan (RP)',
            'Asuransi',
            'Diselesaikan Pada',
            'Oleh Petugas',
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
            'Laporan Penyelesaian Billing Pasien per Petugas',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
