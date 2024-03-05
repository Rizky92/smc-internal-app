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
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
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
            ->when($this->perusahaan !== '-', fn (Builder $q): Builder => $q->where('perusahaan_pasien', $this->perusahaan))
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perPage);
    }

    public function getDataPasienPoliMCUProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->with([
                'pasien',
                'pasien.perusahaan',
                'berkasDigital',
                'poliklinik',
                'penjamin',
            ])
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
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
