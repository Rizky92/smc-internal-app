<?php

namespace App\Livewire\Pages\Keuangan;

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

class LaporanFakturPajak extends Component
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
            'tglAwal'  => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Database\Eloquent\Collection<\App\Models\Perawatan\RegistrasiPasien>
     */
    public function getDataLaporanFakturPajakProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->itemBillingPasien($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak')
            ->layout(BaseLayout::class, ['title' => 'Laporan Item Billing Pasien untuk Faktur Pajak']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->itemBillingPasien($this->tglAwal, $this->tglAkhir)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'no_rawat'       => $model->no_rawat,
                    'tgl_registrasi' => $model->tgl_registrasi,
                    'no_ktp'         => $model->no_ktp,
                    'nm_pasien'      => $model->nm_pasien,
                    'alamat'         => $model->alamat,
                    'no_tlp'         => $model->no_tlp,
                    'status_lanjut'  => $model->status_lanjut,
                    'png_Jawab'      => $model->png_jawab,
                    'status'         => $model->status,
                    'nm_perawatan'   => $model->nm_perawatan,
                    'biaya'          => round($model->biaya, 0),
                    'jumlah'         => round($model->jumlah, 2),
                    'totalbiaya'     => round($model->totalbiaya, 0),
                ])
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Registrasi',
            'NIK',
            'Nama',
            'Alamat',
            'No. Telp',
            'Jenis Perawatan',
            'Jaminan/Asuransi',
            'Kategori',
            'Nama Item',
            'Harga',
            'Jumlah',
            'Total'
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
            'Laporan Item Billing Pasien untuk Faktur Pajak',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
