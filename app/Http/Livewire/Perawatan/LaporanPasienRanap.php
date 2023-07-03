<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\PasienRanap;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPasienRanap extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tanggal;

    /** @var string */
    public $statusPerawatan;

    /** @var bool */
    public $semuaPasien;

    protected function queryString(): array
    {
        return [
            'tanggal'         => ['except' => now()->format('Y-m-d')],
            'statusPerawatan' => ['except' => 'tanggal_masuk', 'as' => 'status_perawatan'],
            'semuaPasien'     => ['except' => false, 'as' => 'tampilkan_semua_pasien'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getLaporanPasienRanapProperty(): Paginator
    {
        return PasienRanap::query()
            ->search($this->cari)
            ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
            ->when(
                $this->semuaPasien,
                fn (Builder $query) => $query->where('status_ranap', '<=', '3'),
                fn (Builder $query) => $query->where('status_ranap', '<=', '2')
            )
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.perawatan.laporan-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Masuk Rawat Inap']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tanggal = now()->format('Y-m-d');
        $this->statusPerawatan = 'tanggal_masuk';
        $this->semuaPasien = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            PasienRanap::query()
                ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
                ->when(
                    $this->semuaPasien,
                    fn (Builder $q): Builder => $q->where('status_ranap', '<=', '3'),
                    fn (Builder $q): Builder => $q->where('status_ranap', '<=', '2')
                )
                ->get([
                    'no_rawat',
                    'tgl_registrasi',
                    'jam_reg',
                    'kelas',
                    'ruangan',
                    'trf_kamar',
                    'no_rkm_medis',
                    'data_pasien',
                    'png_jawab',
                    'nm_poli',
                    'nm_dokter',
                    'stts_pulang',
                    'tgl_masuk',
                    'jam_masuk',
                    'tgl_keluar',
                    'jam_keluar',
                    'dpjp',
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Reg.',
            'Jam Reg.',
            'Kelas',
            'Kamar',
            'Tarif',
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
            'Per ' . carbon($this->tanggal)->translatedFormat('d F Y'),
        ];
    }
}
