<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\Laporan\PasienRanap;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPasienRanap extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public $tanggal;

    public $statusPerawatan;

    public $tampilkanSemuaPasienPerTanggal;

    protected function queryString()
    {
        return [
            'tanggal' => ['except' => now()->format('Y-m-d')],
            'statusPerawatan' => ['except' => 'tanggal_masuk', 'as' => 'status_perawatan'],
            'tampilkanSemuaPasienPerTanggal' => ['except' => false, 'as' => 'tampilkan_semua_pasien'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty()
    {
        return PasienRanap::query()
            ->search($this->cari)
            ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
            ->when(
                $this->tampilkanSemuaPasienPerTanggal,
                fn (Builder $query) => $query->where('status_ranap', '<=', '3'),
                fn (Builder $query) => $query->where('status_ranap', '<=', '2')
            )
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.perawatan.laporan-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Masuk Rawat Inap']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tanggal = now()->format('Y-m-d');
        $this->statusPerawatan = 'tanggal_masuk';
        $this->tampilkanSemuaPasienPerTanggal = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            PasienRanap::query()
                ->search($this->cari)
                ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
                ->when(
                    $this->tampilkanSemuaPasienPerTanggal,
                    fn (Builder $query) => $query->where('status_ranap', '<=', '3'),
                    fn (Builder $query) => $query->where('status_ranap', '<=', '2')
                )
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
