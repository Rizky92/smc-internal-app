<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanFakturPajakUmum extends Component
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

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataLaporanFakturPajakProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->laporanFakturPajakUmum($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak-umum')
            ->layout(BaseLayout::class, ['title' => 'Laporan Faktur Pajak Pasien PRIBADI/UMUM (A09)']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Faktur Pajak' => RegistrasiPasien::query()
                ->laporanFakturPajakUmum($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Faktur Pajak' => [
                'No. Rawat',
                'Kode Transaksi',
                'Tgl. Bayar',
                'Jam Bayar',
                'Jenis Rawat',
                'Jenis Faktur',
                'Keterangan Tambahan',
                'Dokumen Pendukung',
                'Cap Fasilitas',
                'ID TKU Penjual',
                'Jenis ID',
                'Negara',
                'ID TKU',
                'No. RM',
                'NIK Pasien',
                'Nama Pasien',
                'Alamat Pasien',
                'Email Pasien',
                'No. Telp Pasien',
                'Kode Asuransi',
                'Nama Asuransi',
                'Alamat Asuransi',
                'No. Telp Asuransi',
                'Email Asuransi',
                'NPWP Asuransi',
                'Kode Perusahaan',
                'Nama Perusahaan',
                'Alamat Perusahaan',
                'No. Telp Perusahaan',
                'Email Perusahaan',
                'NPWP Perusahaan',
            ],
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
            'Laporan Faktur Pajak Pasien UMUM/PRIBADI (A09)',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
