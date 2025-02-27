<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\TambahanBiaya;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTambahanBiayaPasien extends Component
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
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataTambahanBiayaPasienProperty()
    {
        return $this->isDeferred ? [] : TambahanBiaya::query()
            ->biayaTambahanUntukHonorDokter($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-tambahan-biaya-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Tambahan Biaya Pasien untuk Honor Dokter']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => TambahanBiaya::query()
                ->biayaTambahanUntukHonorDokter($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (TambahanBiaya $model): array => [
                    'tgl_registrasi' => $model->tgl_registrasi,
                    'jam_reg'        => $model->jam_reg,
                    'nm_pasien'      => $model->nm_pasien,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'no_rawat'       => $model->no_rawat,
                    'nama_biaya'     => $model->nama_biaya,
                    'besar_biaya'    => floatval($model->besar_biaya),
                    'png_jawab'      => $model->png_jawab,
                    'dokter_ralan'   => $model->dokter_ralan,
                    'dokter_ranap'   => $model->dokter_ranap,
                    'nm_poli'        => $model->nm_poli,
                    'status_lanjut'  => $model->status_lanjut,
                    'status_bayar'   => $model->status_bayar,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl.',
            'Jam',
            'Nama Pasien',
            'No. RM',
            'No. Registrasi',
            'Nama Biaya',
            'Nominal (RP)',
            'Jenis Bayar',
            'Dokter Ralan',
            'Dokter Ranap',
            'Asal Poli',
            'Jenis Perawatan',
            'Status Pembayaran',
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
            'Laporan Tambahan Biaya Pasien untuk Honor Dokter',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
