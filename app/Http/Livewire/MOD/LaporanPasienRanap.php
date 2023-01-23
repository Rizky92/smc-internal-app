<?php

namespace App\Http\Livewire\MOD;

use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPasienRanap extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $perpage;

    public $tanggal;

    public $statusPerawatan;

    public $riwayatPindahKamar;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'tanggal' => ['except' => now()->format('Y-m-d')],
            'statusPerawatan' => ['except' => 'tanggal_masuk', 'as' => 'status_perawatan'],
            'riwayatPindahKamar' => ['except' => false, 'as' => 'pindah_kamar'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::query()
            ->selectLaporanPasienRanap()
            ->filterLaporanPasienRanap(
                $this->cari,
                $this->tanggal,
                $this->statusPerawatan,
                $this->riwayatPindahKamar
            )
            ->urutkanLaporanPasienRanapBerdasarkan($this->statusPerawatan)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.mod.laporan-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Masuk Rawat Inap']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->tanggal = now()->format('Y-m-d');
        $this->statusPerawatan = 'tanggal_masuk';
        $this->riwayatPindahKamar = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->selectLaporanPasienRanap(true)
                ->filterLaporanPasienRanap('', $this->tanggal, $this->statusPerawatan, $this->riwayatPindahKamar)
                ->urutkanLaporanPasienRanapBerdasarkan($this->statusPerawatan)
                ->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Reg.',
            'Jam Reg.',
            'Kamar',
            'Kelas',
            'No. RM',
            'Pasien',
            'Jenis Bayar',
            'Asal Poli',
            'Dokter Poli',
            'Status',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Keluar',
            'Jam Keluar',
            'DPJP',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Pasien Masuk Rawat Inap',
            Carbon::parse($this->tanggal)->format('d F Y'),
        ];
    }
}
