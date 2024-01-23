<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Models\Farmasi\ResepObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class KunjunganPerBentukObat extends Component
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
    public $jenisKunjungan;

    protected function queryString(): array
    {
        return [
            'jenisKunjungan' => ['except' => '', 'as' => 'jenis_kunjungan'],
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
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Resep']);
    }

    public function getDataKunjunganResepObatRegularProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->kunjunganResep($this->jenisKunjungan, 'umum', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_umum');
    }

    public function getDataKunjunganResepObatRacikanProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->kunjunganResep($this->jenisKunjungan, 'racikan', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_racikan');
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisKunjungan = 'semua';
    }

    public function searchData(): void
    {
        $this->resetPage('page_regular');
        $this->resetPage('page_racikan');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        $map = fn (ResepObat $model): array => [
            'tgl_perawatan' => $model->tgl_perawatan,
            'no_resep'      => $model->no_resep,
            'nm_pasien'     => $model->nm_pasien,
            'png_jawab'     => $model->png_jawab,
            'status_lanjut' => $model->status_lanjut,
            'nm_poli'       => $model->nm_poli,
            'nm_dokter'     => $model->nm_dokter,
            'validasi'      => $model->waktu_validasi,
            'penyerahan'    => $model->waktu_penyerahan,
            'selisih'       => time_length($model->waktu_validasi, $model->waktu_penyerahan),
            'total'         => $model->total,
        ];

        return [
            'Umum' => ResepObat::query()
                ->kunjunganResep($this->jenisKunjungan, 'umum', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
            'Racikan' => ResepObat::query()
                ->kunjunganResep($this->jenisKunjungan, 'racikan', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Resep Farmasi per Bentuk Resep',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}