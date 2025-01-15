<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PenjualanObat;
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
            ->fakturPajakPasien($this->tglAwal, $this->tglAkhir)
            ->unionAll(PenjualanObat::query()->fakturPajakPasien($this->tglAwal, $this->tglAkhir))
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak')
            ->layout(BaseLayout::class, ['title' => 'Laporan Faktur Pajak Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Faktur Pajak' => RegistrasiPasien::query()
                ->fakturPajakPasien($this->tglAwal, $this->tglAkhir)
                ->unionAll(PenjualanObat::query()->fakturPajakPasien($this->tglAwal, $this->tglAkhir))
                ->search($this->cari)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'no_rawat'       => $model->no_rawat,
                    'tgl_registrasi' => $model->tgl_registrasi,
                    'tgl_bayar'      => $model->tgl_bayar,
                    'jam_bayar'      => $model->jam_bayar,
                    'jenis_id'       => $model->jenis_id,
                    'negara'         => $model->negara,
                    'npwp'           => $model->npwp,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'no_ktp'         => $model->no_ktp,
                    'nm_pasien'      => $model->nm_pasien,
                    'alamat'         => $model->alamat,
                    'email'          => $model->email,
                    'no_tlp'         => $model->no_tlp,
                    'status_lanjut'  => $model->status_lanjut,
                ])
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Faktur Pajak' => [
                'No. Rawat',
                'Tgl. Registrasi',
                'Tgl. Pelunasan',
                'Jam Pelunasan',
                'Jenis ID',
                'Negara',
                'No. NPWP',
                'No. RM',
                'NIK',
                'Nama Pasien/Perusahaan',
                'Alamat Pasien/Perusahaan',
                'Email Pasien/Perusahaan',
                'No. Telp Pasien/Perusahaan',
                'Status Registrasi',
            ],
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
            'Laporan Faktur Pajak Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
