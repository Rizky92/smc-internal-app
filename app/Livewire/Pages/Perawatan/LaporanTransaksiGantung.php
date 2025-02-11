<?php

namespace App\Livewire\Pages\Perawatan;

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

class LaporanTransaksiGantung extends Component
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

    /** @var "ralan"|"ranap" */
    public $jenis;

    /** @var string */
    public $status;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'jenis'    => ['except' => 'ralan'],
            'status'   => ['except' => 'sudah'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanTransaksiGantungProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->laporanTransaksiGantung($this->tglAwal, $this->tglAkhir, $this->jenis, $this->status)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.perawatan.laporan-transaksi-gantung')
            ->layout(BaseLayout::class, ['title' => 'Laporan Transaksi Gantung Pasien Rawat Jalan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->jenis = 'ralan';
        $this->status = 'sudah';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->laporanTransaksiGantung($this->tglAwal, $this->tglAkhir, $this->jenis, $this->status)
                ->cursor()
                ->map(fn (RegistrasiPasien $model, int $_): array => [
                    'nm_dokter'      => $model->nm_dokter,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'nm_pasien'      => $model->nm_pasien,
                    'nm_poli'        => $model->nm_poli,
                    'p_jawab'        => $model->p_jawab,
                    'almt_pj'        => $model->almt_pj,
                    'hubunganpj'     => $model->hubunganpj,
                    'penjamin'       => $model->penjamin,
                    'stts'           => $model->stts,
                    'no_rawat'       => $model->no_rawat,
                    'tgl_registrasi' => $model->tgl_registrasi,
                    'jam_reg'        => $model->jam_reg,
                    'diagnosa'       => $model->diagnosa ? 'Ada' : 'Tidak ada',
                    'tindakan'       => $model->ralan_perawat ? 'Ada' : 'Tidak ada',
                    'obat'           => $model->status_resep,
                    'lab'            => $model->status_order_lab,
                    'rad'            => $model->status_order_rad,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Dr. Dituju',
            'No. RM',
            'Nama Pasien',
            'Poliklinik',
            'P. J.',
            'Alamat',
            'Hubungan',
            'Penjamin',
            'Status',
            'No. Rawat',
            'Tgl. Masuk',
            'Jam',
            'Diagnosa',
            'Tindakan',
            'Obat',
            'Laboratorium',
            'Radiologi',
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
            'Laporan Transaksi Gantung Pasien '.($this->status === 'ralan' ? 'Rawat Jalan' : 'Rawat Inap'),
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
