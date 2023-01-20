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

    public $tglAwal;

    public $tglAkhir;

    public $jamAwal;

    public $jamAkhir;

    public $statusPerawatan;

    public $pasienPindahKamar;

    protected $paginationTheme = 'bootstrap';

    protected const JAM_AWAL = '00:00:00';
    protected const JAM_AKHIR = '00:00:00';

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'tglAwal' => ['except' => now()->subDay()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'jamAwal' => ['except' => $this::JAM_AWAL, 'as' => 'jam_awal'],
            'jamAkhir' => ['except' => $this::JAM_AKHIR, 'as' => 'jam_akhir'],
            'statusPerawatan' => ['except' => 'tanggal_masuk', 'as' => 'status_perawatan'],
            'pasienPindahKamar' => ['except' => false, 'as' => 'pindah_kamar'],
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
                $this->tglAwal,
                $this->tglAkhir,
                $this->jamAwal,
                $this->jamAkhir,
                $this->statusPerawatan,
                $this->pasienPindahKamar
            )
            ->orderByColumnsFilterLaporanPasienRanap($this->statusPerawatan)
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
        $this->tglAwal = now()->subDay()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
        $this->jamAwal = $this::JAM_AWAL;
        $this->jamAkhir = $this::JAM_AKHIR;
        $this->statusPerawatan = 'tanggal_masuk';
        $this->pasienPindahKamar = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->selectLaporanPasienRanap()
                ->filterLaporanPasienRanap(
                    '',
                    $this->tglAwal,
                    $this->tglAkhir,
                    $this->jamAwal,
                    $this->jamAkhir,
                    $this->statusPerawatan,
                    $this->pasienPindahKamar
                )
                ->orderBy('no_rawat')
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
            Carbon::parse($this->tglAwal)->format('d F Y'),
        ];
    }
}
