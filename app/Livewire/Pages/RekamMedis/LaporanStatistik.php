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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class LaporanStatistik extends Component
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
            'tglAwal'  => ['except' => now()->startOfWeek()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfWeek()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getDataLaporanStatistikProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->laporanStatistik($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.laporan-statistik')
            ->layout(BaseLayout::class, ['title' => 'Laporan Statistik']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfWeek()->toDateString();
        $this->tglAkhir = now()->endOfWeek()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->laporanStatistik($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    Str::transliterate($model->no_rawat ?? ''),
                    Str::transliterate($model->no_rm ?? ''),
                    Str::transliterate($model->nm_pasien ?? ''),
                    Str::transliterate($model->no_ktp ?? ''),
                    Str::transliterate($model->jk ?? ''),
                    Str::transliterate($model->tgl_lahir ?? ''),
                    Str::transliterate($model->umurdaftar.' '.$model->sttsumur ?? ''),
                    Str::transliterate($model->agama ?? ''),
                    Str::transliterate($model->suku ?? ''),
                    Str::transliterate($model->status_lanjut ?? ''),
                    Str::transliterate($model->ruangan ?? ''),
                    Str::transliterate($model->status_poli ?? ''),
                    Str::transliterate($model->nm_poli ?? ''),
                    Str::transliterate($model->nm_dokter ?? ''),
                    Str::transliterate($model->status ?? ''),
                    Str::transliterate($model->tgl_registrasi ?? ''),
                    Str::transliterate($model->jam_registrasi ?? ''),
                    Str::transliterate($model->tgl_keluar ?? ''),
                    Str::transliterate($model->jam_keluar ?? ''),
                    Str::transliterate($model->diagnosa_awal ?? ''),
                    Str::transliterate($model->icd_diagnosa ?? ''),
                    Str::transliterate($model->diagnosa ?? ''),
                    Str::transliterate($model->kd_tindakan_ralan ?? ''),
                    Str::transliterate($model->nm_tindakan_ralan ?? ''),
                    Str::transliterate($model->kd_tindakan_ranap ?? ''),
                    Str::transliterate($model->nm_tindakan_ranap ?? ''),
                    Str::transliterate($model->lama_operasi ?? ''),
                    Str::transliterate($model->rujukan_masuk ?? ''),
                    Str::transliterate($model->dokter_pj ?? ''),
                    Str::transliterate($model->kelas ?? ''),
                    Str::transliterate($model->penjamin ?? ''),
                    Str::transliterate($model->status_bayar ?? ''),
                    Str::transliterate($model->status_pulang_ranap ?? ''),
                    Str::transliterate($model->rujuk_keluar_rs ?? ''),
                    Str::transliterate($model->alamat ?? ''),
                    Str::transliterate($model->no_hp ?? ''),
                    $model->kunjungan_ke,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No RM',
            'Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Rawat',
            'Kamar',
            'Pasien Lama / Baru',
            'Asal Poli',
            'Dokter Poli',
            'Status Ralan',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD Diagnosa',
            'Diagnosa',
            'Kode Tindakan Ralan',
            'Tindakan Ralan',
            'Kode Tindakan Ranap',
            'Tindakan Ranap',
            'Lama Operasi',
            'Rujukan Masuk',
            'DPJP Ranap',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'Rujukan Keluar',
            'Alamat',
            'No. HP',
            'Kunjungan ke',
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
            'Laporan Statistik Rekam Medis',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
