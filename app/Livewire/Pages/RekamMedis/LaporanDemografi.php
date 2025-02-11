<?php

namespace App\Livewire\Pages\RekamMedis;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanDemografi extends Component
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
     * @return Paginator|array<empty, empty>
     */
    public function getDemografiPasienProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->demografiPasien($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.laporan-demografi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Demografi Pasien']);
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
            fn () => RegistrasiPasien::query()
                ->demografiPasien($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(function (RegistrasiPasien $model): array {
                    $u1 = '0';
                    $u2 = '0';
                    $u3 = '0';
                    $u4 = '0';
                    $u5 = '0';
                    $u6 = '0';
                    $u7 = '0';
                    $u8 = '0';

                    switch (true) {
                        case $model->sttsumur === 'Hr' && $model->umurdaftar < 28:
                            $u1 = '1';
                            // no break
                        case $model->sttsumur === 'Hr' && $model->umurdaftar >= 28:
                            $u2 = '1';
                            // no break
                        case $model->sttsumur === 'Bl' && $model->umurdaftar < 12:
                            $u2 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && between($model->umurdaftar, 1, 4, true):
                            $u3 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && between($model->umurdaftar, 5, 14, true):
                            $u4 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && between($model->umurdaftar, 15, 24, true):
                            $u5 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && between($model->umurdaftar, 25, 44, true):
                            $u6 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && between($model->umurdaftar, 45, 64, true):
                            $u7 = '1';
                            // no break
                        case $model->sttsumur === 'Th' && $model->umurdaftar >= 65:
                            $u8 = '1';
                    }

                    return [
                        'nm_kec'           => $model->nm_kec,
                        'no_rkm_medis'     => $model->no_rkm_medis,
                        'no_rawat'         => $model->no_rawat,
                        'nm_pasien'        => $model->nm_pasien,
                        'tgl_lahir'        => $model->tgl_lahir,
                        'alamat'           => $model->alamat,
                        'umur_kat_1'       => $u1,
                        'umur_kat_2'       => $u2,
                        'umur_kat_3'       => $u3,
                        'umur_kat_4'       => $u4,
                        'umur_kat_5'       => $u5,
                        'umur_kat_6'       => $u6,
                        'umur_kat_7'       => $u7,
                        'umur_kat_8'       => $u8,
                        'pr'               => $model->jk === 'P' ? '1' : '0',
                        'lk'               => $model->lk === 'L' ? '1' : '0',
                        'kd_penyakit'      => $model->kd_penyakit,
                        'nm_penyakit'      => $model->nm_penyakit,
                        'agama'            => $model->agama,
                        'pnd'              => $model->pnd,
                        'nama_bahasa'      => $model->nama_bahasa,
                        'nama_suku_bangsa' => $model->nama_suku_bangsa,
                    ];
                }),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kecamatan',
            'No. RM',
            'No. Registrasi',
            'Pasien',
            'Tgl. Lahir',
            'Alamat',
            '0 - < 28 Hr',
            '28 Hr - 1 Th',
            '1 - 4 Th',
            '5 - 14 Th',
            '15 - 24 Th',
            '25 - 44 Th',
            '45 - 64 Th',
            '> 64 Th',
            'PR',
            'LK',
            'ICD-10',
            'Diagnosa',
            'Agama',
            'Pendidikan',
            'Bahasa',
            'Suku',
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
            'Laporan Demografi Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
