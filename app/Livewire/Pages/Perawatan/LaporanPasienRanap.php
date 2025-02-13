<?php

namespace App\Livewire\Pages\Perawatan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\PasienRanap;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPasienRanap extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tanggal;

    /** @var string */
    public $statusPerawatan;

    /** @var bool */
    public $semuaPasien;

    protected function queryString(): array
    {
        return [
            'tanggal'         => ['except' => now()->toDateString()],
            'statusPerawatan' => ['except' => 'tanggal_masuk', 'as' => 'status_perawatan'],
            'semuaPasien'     => ['except' => false, 'as' => 'tampilkan_semua_pasien'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getLaporanPasienRanapProperty()
    {
        return $this->isDeferred ? [] : PasienRanap::query()
            ->search($this->cari)
            ->where(fn (Builder $q) => $q
                ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
                ->orWhereBetween('tgl_keluar', [$this->tanggal, $this->tanggal]))
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
        return view('livewire.pages.perawatan.laporan-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Masuk Rawat Inap']);
    }

    protected function defaultValues(): void
    {
        $this->tanggal = now()->toDateString();
        $this->statusPerawatan = 'tanggal_masuk';
        $this->semuaPasien = false;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => PasienRanap::query()
                ->search($this->cari)
                ->where(fn ($q) => $q
                    ->whereBetween('tgl_masuk', [$this->tanggal, $this->tanggal])
                    ->orWhereBetween('tgl_keluar', [$this->tanggal, $this->tanggal]))
                ->when(
                    $this->semuaPasien,
                    fn (Builder $query) => $query->where('status_ranap', '<=', '3'),
                    fn (Builder $query) => $query->where('status_ranap', '<=', '2')
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
            'Per '.carbon($this->tanggal)->translatedFormat('d F Y'),
        ];
    }
}
