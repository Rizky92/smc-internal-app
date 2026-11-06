<?php

namespace App\Livewire\Pages\RekamMedis;

use App\Jobs\ExportLaporanStatistikJob;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\Notifications\Notification;
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

    /** @var int */
    public $option;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var int */
    private const EXCEL_EXPORT = 1;

    /** @var int */
    private const BACKGROUND_EXPORT = 2;

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

    public function exportWithOption(int $option): void
    {
        $this->option = $option;

        if ($this->option === self::EXCEL_EXPORT) {
            $this->exportToExcel();
        } elseif ($this->option === self::BACKGROUND_EXPORT) {
            $this->exportToBackground();
        }
    }

    public function exportToBackground(): void
    {
        $userId = user()->nik;

        $payload = [
            'tglAwal'  => $this->tglAwal,
            'tglAkhir' => $this->tglAkhir,
        ];

        ExportLaporanStatistikJob::dispatch($userId, $payload);

        Notification::make()
            ->message('Export Laporan Statistik sedang berjalan')
            ->info()
            ->send(user());

        $this->emit('flash.info', 'Proses export ke Excel telah dimulai, silahkan tunggu beberapa saat.');
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
                    Str::transliterate($this->diagnosaUtama($model->icd_diagnosa ?? '')),
                    Str::transliterate($this->diagnosaSekunder($model->icd_diagnosa ?? '')),
                    Str::transliterate($this->diagnosaUtama($model->diagnosa ?? '')),
                    Str::transliterate($this->diagnosaSekunder($model->diagnosa ?? '')),
                    Str::transliterate($model->icd9_prosedur ?? ''),
                    Str::transliterate($model->kd_tindakan_ralan ?? ''),
                    Str::transliterate($model->nm_tindakan_ralan ?? ''),
                    Str::transliterate($model->kd_tindakan_ranap ?? ''),
                    Str::transliterate($model->nm_tindakan_ranap ?? ''),
                    Str::transliterate($model->lama_operasi ?? ''),
                    Str::transliterate($model->rujukan_masuk ?? ''),
                    Str::transliterate($model->dokter_pj ?? ''),
                    Str::transliterate($model->dpjp_anastesi ?? ''),
                    Str::transliterate($model->kelas ?? ''),
                    Str::transliterate($model->penjamin ?? ''),
                    Str::transliterate($model->status_bayar ?? ''),
                    Str::transliterate($model->status_pulang_ranap ?? ''),
                    Str::transliterate($model->rujuk_keluar_rs ?? ''),
                    Str::transliterate($model->alamat ?? ''),
                    Str::transliterate($model->no_hp ?? ''),
                    $model->kunjungan_ke,
                    Str::transliterate($model->kd_pj ?? ''),
                ]),
        ];
    }

    protected function diagnosaUtama(string $value): string
    {
        return str($value)->split('/(; )/')->first() ?? '';
    }

    protected function diagnosaSekunder(string $value): string
    {
        return str($value)->split('/(; )/')->slice(1)->implode('; ');
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
            'ICD-10 Utama',
            'ICD-10 Sekunder',
            'Diagnosa Utama',
            'Diagnosa Sekunder',
            'ICD-9',
            'Kode Tindakan Ralan',
            'Tindakan Ralan',
            'Kode Tindakan Ranap',
            'Tindakan Ranap',
            'Lama Operasi',
            'Rujukan Masuk',
            'DPJP Ranap',
            'DPJP Anastesi',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'Rujukan Keluar',
            'Alamat',
            'No. HP',
            'Kunjungan ke',
            'Kode Penjamin',
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
