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
use Livewire\Component;

class StatusDataPasien extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    public $tampilkanSemuaRegistrasi;

    protected function queryString()
    {
        return [
            'tampilkanSemuaRegistrasi' => ['except' => false, 'as' => 'semua'],
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataStatusRekamMedisPasienProperty()
    {
        return $this->isDeferred
            ? []
            : RegistrasiPasien::query()
                ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->tampilkanSemuaRegistrasi)
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

    public function render()
    {
        return view('livewire.rekam-medis.status-data-pasien')
            ->layout(BaseLayout::class, ['title' => 'Status Data Rekam Medis Pasien']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->tampilkanSemuaRegistrasi = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->statusDataRM($this->tglAwal, $this->tglAkhir, $this->tampilkanSemuaRegistrasi)
                ->cursor()
                ->map(fn ($v, $k) => [
                    'no_rawat' => $v->no_rawat,
                    'tgl_registrasi' => $v->tgl_registrasi,
                    'stts' => $v->stts,
                    'nm_dokter' => $v->nm_dokter,
                    'no_rkm_medis' => $v->no_rkm_medis,
                    'nm_pasien' => $v->nm_pasien,
                    'nm_poli' => $v->nm_poli,
                    'status_lanjut' => $v->status_lanjut,
                    'soapie_ralan' => boolval($v->soapie_ralan) ? 'Ada' : 'Tidak ada',
                    'soapie_ranap' => boolval($v->soapie_ranap) ? 'Ada' : 'Tidak ada',
                    'resume_ralan' => boolval($v->resume_ralan) ? 'Ada' : 'Tidak ada',
                    'resume_ranap' => boolval($v->resume_ranap) ? 'Ada' : 'Tidak ada',
                    'triase_igd' => boolval($v->triase_igd) ? 'Ada' : 'Tidak ada',
                    'askep_igd' => boolval($v->askep_igd) ? 'Ada' : 'Tidak ada',
                    'icd_10' => boolval($v->icd_10) ? 'Ada' : 'Tidak ada',
                    'icd_9' => boolval($v->icd_9) ? 'Ada' : 'Tidak ada',
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
            'ICD 10',
            'ICD 9',
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
