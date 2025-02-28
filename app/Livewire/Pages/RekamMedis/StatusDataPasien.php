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

class StatusDataPasien extends Component
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

    /** @var bool */
    public $semuaRegistrasi;

    /** @var string */
    public $jenisPerawatan;

    protected function queryString(): array
    {
        return [
            'jenisPerawatan'  => ['except' => 'semua', 'as' => 'jenis_rawat'],
            'semuaRegistrasi' => ['except' => false, 'as' => 'semua'],
            'tglAwal'         => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'        => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getDataStatusRekamMedisPasienProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->semuaRegistrasi)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.status-data-pasien')
            ->layout(BaseLayout::class, ['title' => 'Status Data Rekam Medis Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->semuaRegistrasi = false;
        $this->jenisPerawatan = 'semua';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->semuaRegistrasi)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'no_rawat'       => $model->no_rawat,
                    'tgl_registrasi' => $model->tgl_registrasi,
                    'stts'           => $model->stts,
                    'nm_dokter'      => $model->nm_dokter,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'nm_pasien'      => $model->nm_pasien,
                    'nm_poli'        => $model->nm_poli,
                    'status_lanjut'  => $model->status_lanjut,
                    'soapie_ralan'   => boolval($model->soapie_ralan) ? 'Ada' : 'Tidak ada',
                    'soapie_ranap'   => boolval($model->soapie_ranap) ? 'Ada' : 'Tidak ada',
                    'resume_ralan'   => boolval($model->resume_ralan) ? 'Ada' : 'Tidak ada',
                    'resume_ranap'   => boolval($model->resume_ranap) ? 'Ada' : 'Tidak ada',
                    'triase_igd'     => boolval($model->triase_igd) ? 'Ada' : 'Tidak ada',
                    'askep_igd'      => $model->askep_igd ? 'Ada' : 'Tidak ada',
                    'askep_ralan'    => $model->askep_ralan,
                    'askep_ranap'    => $model->askep_ranap,
                    'asmed_igd'      => $model->asmed_igd ? 'Ada' : 'Tidak ada',
                    'asmed_poli'     => $model->asmed_poli,
                    'asmed_rwi'      => $model->asmed_rwi,
                    'icd_10'         => boolval($model->icd_10) ? 'Ada' : 'Tidak ada',
                    'icd_9'          => boolval($model->icd_9) ? 'Ada' : 'Tidak ada',
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Registrasi',
            'Status',
            'Dokter',
            'No. RM',
            'Pasien',
            'Poliklinik',
            'Jenis Perawatan',
            'S.O.A.P.I.E. Ralan',
            'S.O.A.P.I.E. Ranap',
            'Resume Ralan',
            'Resume Ranap',
            'Triase IGD',
            'Askep IGD',
            'Askep Ralan',
            'Askep Ranap',
            'Asmed IGD',
            'Asmed Poli',
            'Asmed RWI',
            'ICD 10',
            'ICD 9',
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
            'Status Data Rekam Medis Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
