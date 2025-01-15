<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PenjualanObat;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanFakturPajakBPJS extends Component
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
     * @return array<empty, empty>|\Illuminate\Database\Eloquent\Collection<\App\Models\Perawatan\RegistrasiPasien>
     */
    public function getDataLaporanFakturPajakProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->laporanFakturPajakBPJS($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-faktur-pajak-b-p-j-s')
            ->layout(BaseLayout::class, ['title' => 'Laporan Faktur Pajak Pasien BPJS Kesehatan (BPJ)']);
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
                ->laporanFakturPajakBPJS($this->tglAwal, $this->tglAkhir)
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
                'Status Lanjut',
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
                'No. Telpon Pasien',
                'Kode Asuransi',
                'Nama Asuransi',
                'Alamat Asuransi',
                'No. Telpon Asuransi',
                'Email Asuransi',
                'NPWP Asuransi',
                'Kode Perusahaan',
                'Nama Perusahaan',
                'Alamat Perusahaan',
                'No. Telpon Perusahaan',
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
            'Laporan Faktur Pajak Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
