<?php

namespace App\Livewire\Pages\Laboratorium;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Mail\KirimHasilMCU;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Perusahaan;
use App\Models\RekamMedis\Pasien;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class KirimHasilMCUKaryawan extends Component
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
    public $perusahaan;

    /** @var array */
    public $checkedPasien;

    protected function queryString(): array
    {
        return [
            'tglAwal'    => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'   => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
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
            ->paginate($this->perpage);
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
            // ->whereHas('berkasDigital', fn (Builder $q): Builder => $q->where('kode', '016'))
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
        return view('livewire.pages.laboratorium.kirim-hasil-mcu-karyawan')
            ->layout(BaseLayout::class, ['title' => 'Kirim Hasil MCU Karyawan via Email']);
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function previewEmail()
    {
        return class_basename(KirimHasilMCU::class);
    }

    public function sendEmail(): void
    {
        dd($this->checkedPasien);
        // $dataPerawatan = RegistrasiPasien::query()
        //     ->
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->checkedPasien = [];
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
