<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\ResepObat;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class RincianKunjunganRalan extends Component
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
    public $totalHarga;

    protected function queryString(): array
    {
        return [
            'tglAwal'    => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'   => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'totalHarga' => ['except' => '', 'as' => 'total_harga'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getRincianKunjunganRalanProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->with('pemberian', 'pemberian.obat')
            ->rincianKunjunganRalan($this->tglAwal, $this->tglAkhir)
            ->when($this->totalHarga === 'below_100k', fn (Builder $q): Builder => $q->having('total_harga', '<', 100000))
            ->when($this->totalHarga === 'above_100k', fn (Builder $q): Builder => $q->having('total_harga', '>=', 100000))
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.rincian-kunjungan-ralan')
            ->layout(BaseLayout::class, ['title' => 'Rincian Kunjungan Ralan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->totalHarga = 'below_100k';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        $data = ResepObat::query()
            ->with(['pemberian.obat'])
            ->rincianKunjunganRalan($this->tglAwal, $this->tglAkhir, $this->totalHarga)
            ->when($this->totalHarga === 'below_100k', fn ($q) => $q->having('total_harga', '<', 100000))
            ->when($this->totalHarga === 'above_100k', fn ($q) => $q->having('total_harga', '>=', 100000))
            ->search($this->cari)
            ->cursor()
            ->map(fn (ResepObat $model): array => [
                'tgl_perawatan' => $model->tgl_perawatan,
                'no_resep'      => $model->no_resep,
                'no_rawat'      => $model->no_rawat,
                'nm_pasien'     => $model->nm_pasien,
                'png_jawab'     => $model->png_jawab,
                'nm_dokter'     => $model->nm_dokter,
                'kode_brng'     => $model->kode_brng,
                'nama_brng'     => $model->nama_brng,
                'biaya_obat'    => $model->biaya_obat,
                'jml'           => $model->jml,
                'total'         => $model->total,
            ]);

        // Grup data berdasarkan 'no_resep'
        $groupedData = $data->groupBy('no_resep');

        // Hitung total dan tambahkan row total untuk setiap grup
        $groupedData = $groupedData->map(function ($group) {
            // Hitung total dari 'total' untuk grup ini
            $totalSum = $group->sum('total');

            // Tambahkan row total ke grup ini
            $group->push([
                'tgl_perawatan' => 'Total',
                'no_resep'      => '', // Ambil no_resep dari item pertama di grup
                'no_rawat'      => '',
                'nm_pasien'     => '',
                'png_jawab'     => '',
                'nm_dokter'     => '',
                'kode_brng'     => '',
                'nama_brng'     => '',
                'biaya_obat'    => '',
                'jml'           => '',
                'total'         => $totalSum,
            ]);

            return $group;
        });

        // Gabungkan semua grup kembali menjadi satu koleksi
        $data = $groupedData->collapse();

        return [$data];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tanggal Perawatan',
            'No. Resep',
            'No. Rawat',
            'Pasien',
            'Jenis Bayar',
            'Dokter',
            'Kode Obat',
            'Nama Obat',
            'Harga Obat',
            'Jumlah',
            'Total Harga',
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
            'Rincian Kunjungan Ralan ',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
