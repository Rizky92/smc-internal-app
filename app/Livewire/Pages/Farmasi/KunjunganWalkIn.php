<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PenjualanObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class KunjunganWalkIn extends Component
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

    public function getDataKunjunganWalkInProperty()
    {
        return $this->isDeferred ? [] : PenjualanObat::query()
            ->kunjunganWalkInHariIni($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.kunjungan-walk-in')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Walk In']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => PenjualanObat::query()
                ->kunjunganWalkInHariIni($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->map(fn (PenjualanObat $model): array => [
                    'nota_jual'    => $model->nota_jual,
                    'no_rkm_medis' => $model->no_rkm_medis,
                    'nm_pasien'    => $model->nm_pasien,
                    'alamat'       => $model->alamat,
                    'tgl_jual'     => $model->tgl_jual,
                    'jumlah'       => $model->jumlah,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Nota Jual',
            'No RM',
            'Nama Pasien',
            'Alamat',
            'Tanggal Jual',
            'Jumlah',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode: '.$periodeAwal->translatedFormat('d F Y').' - '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Walk In',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
