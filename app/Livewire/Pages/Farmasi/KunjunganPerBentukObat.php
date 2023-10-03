<?php

namespace App\Livewire\Pages\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class KunjunganPerBentukObat extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $jenisPerawatan;

    protected function queryString(): array
    {
        return [
            'jenisPerawatan' => ['except' => '', 'as' => 'jenis_perawatan'],
            'tglAwal'        => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.kunjungan-per-bentuk-obat')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Obat']);
    }

    public function getDataKunjunganResepObatRegularProperty(): Paginator
    {
        return ResepDokter::query()
            ->kunjunganResepObatRegular($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->cari)
            ->sortWithColumns($this->sortColumns, [
                'total' => DB::raw('round(sum(resep_dokter.jml * databarang.h_beli))'),
            ])
            ->paginate($this->perpage, ['*'], 'page_regular');
    }

    public function getDataKunjunganResepObatRacikanProperty(): Paginator
    {
        return ResepDokterRacikan::query()
            ->kunjunganResepObatRacikan($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan, $this->cari)
            ->sortWithColumns($this->sortColumns, [
                'total' => DB::raw('round(sum(resep_dokter_racikan_detail.jml * databarang.h_beli))'),
            ])
            ->paginate($this->perpage, ['*'], 'page_racikan');
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';
    }

    public function searchData(): void
    {
        $this->resetPage('page_regular');
        $this->resetPage('page_racikan');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Obat Regular' => ResepDokter::query()
                ->kunjunganResepObatRegular($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
                ->get()
                ->map(fn (ResepDokter $model): array => [
                    'tgl_perawatan' => $model->tgl_perawatan->format('Y-m-d'),
                    'no_resep'      => $model->no_resep,
                    'nm_pasien'     => $model->nm_pasien,
                    'png_jawab'     => $model->png_jawab,
                    'status_lanjut' => $model->status_lanjut,
                    'nm_poli'       => $model->nm_poli,
                    'nm_dokter'     => $model->nm_dokter,
                    'validasi'      => $model->waktu_validasi->translatedFormat('Y-m-d H:i:s'),
                    'penyerahan'    => optional($model->waktu_penyerahan)->translatedFormat('Y-m-d H:i:s'),
                    'selisih'       => time_length($model->waktu_validasi, $model->waktu_penyerahan),
                    'total'         => floatval($model->total),
                ]),

            'Obat Racikan' => ResepDokterRacikan::query()
                ->kunjunganResepObatRacikan($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
                ->get()
                ->map(fn (ResepDokterRacikan $model): array => [
                    'tgl_perawatan' => $model->tgl_perawatan->format('Y-m-d'),
                    'no_resep'      => $model->no_resep,
                    'nm_pasien'     => $model->nm_pasien,
                    'png_jawab'     => $model->png_jawab,
                    'status_lanjut' => $model->status_lanjut,
                    'nm_poli'       => $model->nm_poli,
                    'nm_dokter'     => $model->nm_dokter,
                    'validasi'      => $model->waktu_validasi->translatedFormat('Y-m-d H:i:s'),
                    'penyerahan'    => optional($model->waktu_penyerahan)->translatedFormat('Y-m-d H:i:s'),
                    'selisih'       => time_length($model->waktu_validasi, $model->waktu_penyerahan),
                    'total'         => floatval($model->total),
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tanggal',
            'No. Resep',
            'Pasien',
            'Jenis Bayar',
            'Jenis Perawatan',
            'Asal Poli',
            'Dokter Peresep',
            'Waktu Validasi',
            'Waktu Penyerahan',
            'Lama Penyelesaian',
            'Total Pembelian (RP)',
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
            'Laporan Kunjungan Resep Farmasi per Bentuk Obat',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
