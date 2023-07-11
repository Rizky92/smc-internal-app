<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTransaksiGantung extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var "ralan"|"ranap" */
    public $status;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'status' => ['except' => 'ralan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanTransaksiGantungProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->laporanTransaksiGantung($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.kd_poli',
                'reg_periksa.p_jawab',
                'reg_periksa.almt_pj',
                'reg_periksa.kd_pj',
                'pasien.nm_pasin',
                'coalesce(penjab.nama_perusahaan, penjab.png_jawab, "-")',
                'dokter.nm_dokter',
                'poliklinik.nm_poli',
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.laporan-transaksi-gantung')
            ->layout(BaseLayout::class, ['title' => 'Laporan Transaksi Gantung Pasien Rawat Jalan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->status = 'ralan';
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->laporanTransaksiGantung($this->tglAwal, $this->tglAkhir, $this->status)
                ->get()
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
                    'tindakan'       => ($model->ralan_dokter || $model->ralan_dokter_perawat) ? 'Ada' : 'Tidak ada',
                    'obat'           => $model->obat ? 'Ada' : 'Tidak ada',
                    'lab'            => $model->lab ? 'Ada' : 'Tidak ada',
                    'rad'            => $model->rad ? 'Ada' : 'Tidak ada',
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

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Transaksi Gantung Pasien ' . ($this->status === 'ralan' ? 'Rawat Jalan' : 'Rawat Inap'),
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
