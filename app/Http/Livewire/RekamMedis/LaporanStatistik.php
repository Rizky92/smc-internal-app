<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\StatistikRekamMedis;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;

class LaporanStatistik extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataLaporanStatistikProperty()
    {
        return $this->isDeferred
            ? []
            : StatistikRekamMedis::query()
                ->search($this->cari)
                ->whereBetween('tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
                ->orderBy('no_rawat')
                ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-statistik')
            ->layout(BaseLayout::class, ['title' => 'Laporan Statistik']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            StatistikRekamMedis::query()
                ->whereBetween('tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
                ->orderBy('no_rawat')
                ->cursor()
                ->map(fn (StatistikRekamMedis $model) => [
                    Str::transliterate($model->no_rawat ?? ''),
                    Str::transliterate($model->no_rm ?? ''),
                    Str::transliterate($model->nm_pasien ?? ''),
                    Str::transliterate($model->no_ktp ?? ''),
                    Str::transliterate($model->jk ?? ''),
                    Str::transliterate($model->tgl_lahir ?? ''),
                    Str::transliterate($model->umur ?? ''),
                    Str::transliterate($model->agama ?? ''),
                    Str::transliterate($model->suku ?? ''),
                    Str::transliterate($model->status_lanjut ?? ''),
                    Str::transliterate($model->status_poli ?? ''),
                    Str::transliterate($model->nm_poli ?? ''),
                    Str::transliterate($model->nm_dokter ?? ''),
                    Str::transliterate($model->status ?? ''),
                    Str::transliterate($model->tgl_registrasi ?? ''),
                    Str::transliterate($model->jam_registrasi ?? ''),
                    Str::transliterate($model->tgl_keluar ?? ''),
                    Str::transliterate($model->jam_keluar ?? ''),
                    Str::transliterate($model->diagnosa_awal ?? ''),
                    Str::transliterate($model->kd_diagnosa ?? ''),
                    Str::transliterate($model->nm_diagnosa ?? ''),
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
                    Str::transliterate($model->kunjungan_ke ?? ''),
                ])
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
            'Jenis Perawatan',
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
        return [
            'RS Samarinda Medika Citra',
            'Laporan Statistik Rekam Medis',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
