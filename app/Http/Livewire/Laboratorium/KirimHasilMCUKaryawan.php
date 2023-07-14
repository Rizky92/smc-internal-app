<?php

namespace App\Http\Livewire\Laboratorium;

use App\Mail\KirimHasilMCU;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Perusahaan;
use App\Models\RekamMedis\Pasien;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class KirimHasilMCUKaryawan extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
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
            ->when($this->perusahaan !== '-', fn (Builder $q): Builder => 
                $q->where('perusahaan_pasien', $this->perusahaan))
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
        return view('livewire.laboratorium.kirim-hasil-mcu-karyawan')
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
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
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
