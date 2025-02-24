<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\PenguranganBiaya;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPotonganBiayaPasien extends Component
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

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-potongan-biaya-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Potongan Biaya Pasien']);
    }

    public function getDataPotonganBiayaPasienProperty()
    {
        return $this->isDeferred ? [] : PenguranganBiaya::query()
            ->potonganBiayaPasien($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
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
            fn () => PenguranganBiaya::query()
                ->potonganBiayaPasien($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PenguranganBiaya $model): array => [
                    'tgl_registrasi'    => $model->tgl_registrasi,
                    'jam_reg'           => $model->jam_reg,
                    'nm_pasien'         => $model->nm_pasien,
                    'no_rkm_medis'      => $model->no_rkm_medis,
                    'no_rawat'          => $model->no_rawat,
                    'nama_pengurangan'  => $model->nama_pengurangan,
                    'besar_pengurangan' => floatval($model->besar_pengurangan),
                    'png_jawab'         => $model->png_jawab,
                    'dokter_ralan'      => $model->dokter_ralan,
                    'dokter_ranap'      => $model->dokter_ranap,
                    'nm_poli'           => $model->nm_poli,
                    'status_lanjut'     => $model->status_lanjut,
                    'status_bayar'      => $model->status_bayar,
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
            'Nama Potongan',
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
            'Laporan Pengurangan Biaya Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
