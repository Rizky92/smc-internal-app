<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\LazyCollection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StatusDataPasien extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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
            'tglAwal'         => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'        => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getDataStatusRekamMedisPasienProperty()
    {
        return $this->isDeferred
            ? []
            : RegistrasiPasien::query()
                ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->semuaRegistrasi)
                ->search($this->cari, [
                    'reg_periksa.no_rawat',
                    'reg_periksa.tgl_registrasi',
                    'reg_periksa.stts',
                    'dokter.nm_dokter',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'poliklinik.nm_poli',
                    'reg_periksa.status_lanjut',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.rekam-medis.status-data-pasien')
            ->layout(BaseLayout::class, ['title' => 'Status Data Rekam Medis Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->semuaRegistrasi = false;
        $this->jenisPerawatan = 'semua';
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->semuaRegistrasi)
                ->cursor()
                ->map(fn (RegistrasiPasien $modal): array => [
                    'no_rawat'         => $modal->no_rawat,
                    'tgl_registrasi'   => $modal->tgl_registrasi,
                    'stts'             => $modal->stts,
                    'nm_dokter'        => $modal->nm_dokter,
                    'no_rkm_medis'     => $modal->no_rkm_medis,
                    'nm_pasien'        => $modal->nm_pasien,
                    'nm_poli'          => $modal->nm_poli,
                    'status_lanjut'    => $modal->status_lanjut,
                    'soapie_ralan'     => boolval($modal->soapie_ralan) ? 'Ada' : 'Tidak ada',
                    'soapie_ranap'     => boolval($modal->soapie_ranap) ? 'Ada' : 'Tidak ada',
                    'resume_ralan'     => boolval($modal->resume_ralan) ? 'Ada' : 'Tidak ada',
                    'resume_ranap'     => boolval($modal->resume_ranap) ? 'Ada' : 'Tidak ada',
                    'triase_igd'       => boolval($modal->triase_igd) ? 'Ada' : 'Tidak ada',
                    'askep_igd'        => boolval($modal->askep_igd) ? 'Ada' : 'Tidak ada',
                    'icd_10'           => boolval($modal->icd_10) ? 'Ada' : 'Tidak ada',
                    'icd_9'            => boolval($modal->icd_9) ? 'Ada' : 'Tidak ada',
                    'awal_keperawatan' => $modal->awal_keperawatan,
                ])
                ->all(),
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
            'ICD 10',
            'ICD 9',
            'Awal Keperawatan',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Status Data Rekam Medis Pasien',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
