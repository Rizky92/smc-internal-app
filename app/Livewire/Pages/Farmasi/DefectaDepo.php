<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class DefectaDepo extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tanggal;

    /** @var "Pagi"|"Siang"|"Malam" */
    public $shift;

    /** @var "IFA"|"IFG"|"IFI"|"KO" */
    public $bangsal;

    protected function queryString(): array
    {
        return [
            'tanggal' => ['except' => now()->toDateString(), 'as' => 'tgl_awal'],
            'shift'   => ['except' => $this->dataShiftKerja()->shift, 'as' => 'shift_kerja'],
            'bangsal' => ['as' => 'depo'],
        ];
    }

    protected function dataShiftKerja(): object
    {
        $waktuShiftSemua = Cache::remember('waktu_shift_semua', now()->addWeek(), fn () => DB::connection('mysql_sik')
            ->table('closing_kasir')
            ->get());

        if (! empty($this->shift)) {
            return $waktuShiftSemua
                ->filter(fn ($waktuShift) => $waktuShift->shift === $this->shift)
                ->first();
        }

        return $waktuShiftSemua
            ->filter(fn ($waktuShift) => now()->floorHour()->diffInHours(
                now()->setHour($waktuShift->jam_masuk), false
            ) <= 0)
            ->first();
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataDefectaDepoProperty()
    {
        return $this->isDeferred ? [] : GudangObat::query()
            ->defectaDepo($this->tanggal, $this->shift, $this->bangsal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.defecta-depo')
            ->layout(BaseLayout::class, ['title' => 'Defecta per Depo Farmasi']);
    }

    protected function defaultValues(): void
    {
        $this->tanggal = now()->toDateString();
        $this->shift = $this->dataShiftKerja()->shift;
        $this->bangsal = 'IFA';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => GudangObat::query()
                ->defectaDepo($this->tanggal, $this->shift, $this->bangsal)
                ->cursor(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Satuan',
            'Stok Sekarang',
            'Jumlah Pemakaian per Shift',
            'Jumlah Pemakaian 3 Hari Terakhir',
            'Jumlah Pemakaian 6 Hari Terakhir',
            'Sisa 6 Hari',
        ];
    }

    protected function pageHeaders(): array
    {
        $periode = 'Tgl. '.carbon($this->tanggal)->translatedFormat('d F Y');

        $gudang = [
            'IFA' => 'Farmasi A',
            'IFG' => 'Farmasi IGD',
            'IFI' => 'Farmasi Rawat Inap',
            'KO'  => 'Kamar Operasi OK',
        ];

        $shift = $this->dataShiftKerja();

        return [
            'RS Samarinda Medika Citra',
            'Defecta Depo '.$gudang[$this->bangsal],
            sprintf('Shift kerja %s (%s s.d. %s)', $this->shift, $shift->jam_masuk, $shift->jam_pulang),
            $periode,
        ];
    }
}
