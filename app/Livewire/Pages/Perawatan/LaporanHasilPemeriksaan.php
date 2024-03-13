<?php

namespace App\Livewire\Pages\Perawatan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Perusahaan;
use App\Models\RekamMedis\Pasien;
use App\Models\Laboratorium\HasilPeriksaLab;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class LaporanHasilPemeriksaan extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $perusahaan;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'perusahaan' => ['except' => '-'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPasienProperty(): Paginator
    {
        return Pasien::query()
            ->with(['perusahaan'])
            ->search($this->cari)
            ->when($this->perusahaan !== '-', fn (Builder $q): Builder =>$q->where('perusahaan_pasien', $this->perusahaan))
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function getDataPasienPoliMCUProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->with([
                'pasien',
                'pasien.perusahaan',
                'poliklinik',
                'penjamin',
            ])
            ->whereBetween('tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
            ->where('kd_poli', 'U0036')
            ->when($this->perusahaan !== '-', fn (Builder $q): Builder => $q->whereRelation('pasien.perusahaan', 'perusahaan_pasien', $this->perusahaan))
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function getDataPerusahaanProperty(): Collection
    {
        return Perusahaan::pluck('nama_perusahaan', 'kode_perusahaan');
    }

    public function getPemeriksaanProperty()
    {
        $data = HasilPeriksaLab::query()
            ->laporanTindakanLabDetail($this->tglAwal, $this->tglAkhir)
            ->where('kd_poli', 'U0036')
            ->select('Pemeriksaan', 'satuan', 'nilai_rujukan', 'nilai')
            ->search($this->cari)
            ->get();

        $pemeriksaan = [];

        foreach ($data as $key) {
            $pemeriksaan[$key['Pemeriksaan']] = [
                'Periksa' => $key['Pemeriksaan'],
                'Satuan' => $key['satuan'],
                'Nilai Rujukan' => $key['nilai_rujukan'],
                'Nilai' => $key['nilai'],
            ];
        }

        return $pemeriksaan;
    }

    public function render(): View
    {
        return view('livewire.pages.perawatan.laporan-hasil-pemeriksaan')
            ->layout(BaseLayout::class, ['title' => 'Laporan Hasil Pemeriksaan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perusahaan = '-';
    }

    protected function dataPerSheet(): array
    {
        $headers = ['Satuan', 'Nilai Rujukan', 'Nilai'];

        $data = array_map(function ($header) {
            return array_map(function ($pemeriksaan) use ($header) {
                return $pemeriksaan[$header];
            }, $this->pemeriksaan);
        }, $headers);

        dump([$data]);

        return [$data];
    }
 
    protected function columnHeaders(): array
    {
        $columnHeaders = array_merge([
            'tindakan'
        ], array_keys($this->pemeriksaan));

        return $columnHeaders;
    }

    protected function pageHeaders(): array
    {
        $perusahaan = $this->perusahaan === '-' ? 'Semua Perusahaan' : Perusahaan::find($this->perusahaan)->nama_perusahaan;

        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s/d ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Hasil Pemeriksaan '. $perusahaan,
            now()->translatedFormat('d F Y'),
            $periode,
        ];

    }
}
