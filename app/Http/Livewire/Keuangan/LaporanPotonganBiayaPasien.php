<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PenguranganBiaya;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPotonganBiayaPasien extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.keuangan.laporan-potongan-biaya-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Potongan Biaya Pasien']);
    }

    public function getDataPotonganBiayaPasienProperty(): Paginator
    {
        return PenguranganBiaya::query()
            ->potonganBiayaPasien($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                "pasien.nm_pasien",
                "reg_periksa.no_rkm_medis",
                "pengurangan_biaya.no_rawat",
                "pengurangan_biaya.nama_pengurangan",
                "penjab.png_jawab",
                "dokter.nm_dokter",
                "coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')",
                "poliklinik.nm_poli",
                "reg_periksa.status_lanjut",
                "reg_periksa.status_bayar",
            ])
            ->sortWithColumns($this->sortColumns, [
                'dokter_ralan' => DB::raw("dokter.nm_dokter"),
                'dokter_ranap' => DB::raw("coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')"),
            ])
            ->paginate($this->perpage);
    }

    protected function defaultValues(): void
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
            PenguranganBiaya::query()
                ->potonganBiayaPasien($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (PenguranganBiaya $model): array => [
                    'tgl_registrasi'    => $model->tgl_registrasi,
                    'jam_reg'           => $model->jam_reg,
                    'nm_pasien'         => $model->nm_pasien,
                    'no_rkm_medis'      => $model->no_rkm_medis,
                    'no_rawat'          => $model->no_rawat,
                    'nama_pengurangan'  => $model->nama_pengurangan,
                    'besar_pengurangan' => floatval($model->besar_pengurangan),
                    'png_jawab'         => $model->png_jawab,
                    'dokter_ralan'      => $model->dokter_ralan,
                    'dokter_ranap'      => $model->dokter_ranap,
                    'nm_poli'           => $model->nm_poli,
                    'status_lanjut'     => $model->status_lanjut,
                    'status_bayar'      => $model->status_bayar,
                ])
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl.',
            'Jam',
            'Nama Pasien',
            'No. RM',
            'No. Registrasi',
            'Nama Potongan',
            'Nominal (RP)',
            'Jenis Bayar',
            'Dokter Ralan',
            'Dokter Ranap',
            'Asal Poli',
            'Jenis Perawatan',
            'Status Pembayaran',
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
            'Laporan Pengurangan Biaya Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
