<?php

namespace App\Livewire\Pages\Casemix;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Casemix\BridgingSep;
use App\View\Components\BaseLayout;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPasienRanapPulang extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tahun;

    /** @var string */
    public $bulan;

    protected function queryString(): array
    {
        return [
            'tahun' => ['except' => '', 'as' => 'tahun'],
            'bulan' => ['except' => '', 'as' => 'bulan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : BridgingSep::query()
            ->pasienRanapPulang($this->tahun.'-'.$this->bulan)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.casemix.laporan-pasien-ranap-pulang')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Ranap Pulang Bulan Berikutnya']);
    }

    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2022, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function getDataBulanProperty(): array
    {
        return [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->subMonth()->format('Y');
        $this->bulan = now()->subMonth()->format('m');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => BridgingSep::query()
                ->pasienRanapPulang($this->tahun.'-'.$this->bulan)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (BridgingSep $model): array => [
                    'No. SEP'              => $model->no_sep,
                    'No. Rawat'            => $model->no_rawat,
                    'Tgl. Registrasi'      => $model->tgl_registrasi,
                    'No. RM'               => $model->no_rm,
                    'Nama Pasien'          => $model->nama_pasien,
                    'Jenis Pelayanan SEP'  => $model->jenis_pelayanan,
                    'Status Lanjut'        => $model->status_lanjut,
                    'Kode Poli Asal'       => $model->kd_poli,
                    'Nama Poli Asal'       => $model->nm_poli,
                    'Kode Jenis Bayar'     => $model->kd_pj,
                    'Nama Jenis Bayar'     => $model->jenis_bayar,
                    'Kelas Rawat'          => $model->kls_rawat,
                    'Tgl. SEP'             => $model->tglsep,
                    'Tgl. Pulang SEP'      => $model->tglpulang,
                    'Tgl. Masuk Ranap'     => $model->tgl_masuk_ranap,
                    'Tgl. Keluar Ranap'    => $model->tgl_keluar_ranap,
                    'Kamar Terakhir'       => $model->kamar_terakhir,
                    'Tgl. Close Billing'   => $model->tgl_close_billing,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. SEP',
            'No. Rawat',
            'Tgl. Registrasi',
            'No. RM',
            'Nama Pasien',
            'Jenis Pelayanan SEP',
            'Status Lanjut',
            'Kode Poli Asal',
            'Nama Poli Asal',
            'Kode Jenis Bayar',
            'Nama Jenis Bayar',
            'Kelas Rawat',
            'Tgl. SEP',
            'Tgl. Pulang SEP',
            'Tgl. Masuk Ranap',
            'Tgl. Keluar Ranap',
            'Kamar Terakhir',
            'Tgl. Close Billing',
        ];
    }

    protected function pageHeaders(): array
    {
        $date = Carbon::parse($this->tahun.'-'.$this->bulan.'-01');
        $prevMonth = $date->copy()->subMonth();

        $periode = 'SEP '.$prevMonth->translatedFormat('F Y').' / Pulang '.$date->translatedFormat('F Y');

        return [
            'RS Samarinda Medika Citra',
            'Laporan Pasien Ranap Pulang Bulan Berikutnya',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
