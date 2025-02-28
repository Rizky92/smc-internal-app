<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class DaftarRiwayatObatAlkes extends Component
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

    /** @var bool */
    public $barangNol;

    protected function queryString(): array
    {
        return [
            'tglAwal'   => ['except' => now()->subYear()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'  => ['except' => now()->toDateString(), 'as' => 'tgl_akhir'],
            'barangNol' => ['except' => false, 'as' => 'barang_nol'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.daftar-riwayat-obat-alkes')
            ->layout(BaseLayout::class, ['title' => 'Daftar Riwayat Obat/Alkes']);
    }

    public function getDataRiwayatObatProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('obat', $this->tglAwal, $this->tglAkhir, $this->barangNol)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_obat');
    }

    public function getDataRiwayatAlkesProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('alkes', $this->tglAwal, $this->tglAkhir, $this->barangNol)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_alkes');
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->subYear()->toDateString();
        $this->tglAkhir = now()->toDateString();
        $this->barangNol = false;
    }

    protected function dataPerSheet(): array
    {
        $map = fn (Obat $model): array => [
            'kode_brng'                   => $model->kode_brng,
            'nama_brng'                   => $model->nama_brng,
            'stok_akhir'                  => $model->stok_akhir,
            'order_terakhir'              => $model->order_terakhir,
            'penggunaan_terakhir'         => $model->penggunaan_terakhir,
            'tanggal_order_terakhir'      => $model->tanggal_order_terakhir,
            'tanggal_penggunaan_terakhir' => $model->tanggal_penggunaan_terakhir,
            'posisi_order_terakhir'       => $model->posisi_order_terakhir,
            'posisi_penggunaan_terakhir'  => $model->posisi_penggunaan_terakhir,
        ];

        return [
            'obat' => fn () => Obat::query()
                ->daftarRiwayat('obat', $this->tglAwal, $this->tglAkhir, $this->barangNol)
                ->cursor()
                ->map($map),
            'alkes' => fn () => Obat::query()
                ->daftarRiwayat('alkes', $this->tglAwal, $this->tglAkhir, $this->barangNol)
                ->cursor()
                ->map($map),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Item',
            'Nama Item',
            'Stok Akhir',
            'Order Terakhir',
            'Penggunaan Terakhir',
            'Tanggal Order Terakhir',
            'Tanggal Penggunaan Terakhir',
            'Posisi Order Terakhir',
            'Posisi Penggunaan Terakhir',
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
            'Laporan Daftar Riwayat Obat/Alkes',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
