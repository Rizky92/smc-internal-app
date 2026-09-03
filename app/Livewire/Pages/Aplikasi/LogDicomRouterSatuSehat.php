<?php

namespace App\Livewire\Pages\Aplikasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Bridging\DicomRouterWebhookLog;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LogDicomRouterSatuSehat extends Component
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

    /** @var string */
    public $stage;

    /** @var string */
    public $hasil;

    protected function queryString(): array
    {
        return [
            'stage'    => ['except' => 'semua'],
            'hasil'    => ['except' => 'semua'],
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
    public function getLogPengirimanDicomProperty()
    {
        return $this->isDeferred ? [] : DicomRouterWebhookLog::query()
            ->laporanPengirimanDicom($this->tglAwal, $this->tglAkhir, $this->stage, $this->hasil)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, ['created_at' => 'desc'])
            ->paginate($this->perpage);
    }

    /**
     * @return array{total: int, berhasil: int, gagal: int}|array<empty, empty>
     */
    public function getRingkasanPengirimanProperty(): array
    {
        return $this->isDeferred
            ? []
            : DicomRouterWebhookLog::ringkasanPengiriman($this->tglAwal, $this->tglAkhir, $this->stage, $this->hasil);
    }

    /**
     * @return array<string, string>
     */
    public function getPilihanStageProperty(): array
    {
        return ['semua' => 'Semua Tahapan'] + DicomRouterWebhookLog::STAGES;
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.log-dicom-router-satu-sehat')
            ->layout(BaseLayout::class, ['title' => 'Log Pengiriman DICOM ke Satu Sehat']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->stage = 'semua';
        $this->hasil = 'semua';
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => DicomRouterWebhookLog::query()
                ->laporanPengirimanDicom($this->tglAwal, $this->tglAkhir, $this->stage, $this->hasil)
                ->sortWithColumns($this->sortColumns, ['created_at' => 'desc'])
                ->cursor()
                ->map(fn (DicomRouterWebhookLog $model): array => [
                    'waktu'              => carbon($model->created_at)->format('d-m-Y H:i:s'),
                    'hasil'              => $model->status ? 'Berhasil' : 'Gagal',
                    'stage'              => $model->stage_label,
                    'no_rawat'           => $model->no_rawat,
                    'no_rkm_medis'       => $model->no_rkm_medis,
                    'nm_pasien'          => $model->nm_pasien,
                    'dokter_perujuk'     => $model->dokter_perujuk,
                    'noorder'            => $model->noorder ?? '-',
                    'accession_number'   => $model->accession_number ?? '-',
                    'imaging_study_id'   => $model->imaging_study_id ?? '-',
                    'study_instance_uid' => $model->study_instance_uid ?? '-',
                    'keterangan'         => $model->error_message ?? $model->message ?? '-',
                    'error_code'         => $model->error_code ?? '-',
                    'delivery_count'     => $model->delivery_count,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Waktu Diterima',
            'Hasil',
            'Tahapan',
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Dokter Perujuk',
            'No. Order',
            'Accession Number',
            'ImagingStudy ID',
            'Study Instance UID',
            'Keterangan',
            'Kode Error',
            'Jml. Kiriman',
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
            'Log Pengiriman DICOM ke Satu Sehat',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
