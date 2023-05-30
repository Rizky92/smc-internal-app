<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PiutangPasien;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PiutangBelumLunas extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $penjamin;

    protected function queryString(): array
    {
        return [
            'penjamin' => ['except' => ''],
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getDataPiutangBelumLunasProperty()
    {
        return $this->isDeferred
            ? []
            : PiutangPasien::query()
                ->piutangBelumLunas($this->tglAwal, $this->tglAkhir, $this->penjamin)
                ->search($this->cari, [
                    'piutang_pasien.no_rawat',
                    'piutang_pasien.tgl_piutang',
                    'piutang_pasien.no_rkm_medis',
                    'pasien.nm_pasien',
                    'piutang_pasien.status',
                    'piutang_pasien.totalpiutang',
                    'piutang_pasien.uangmuka',
                    'piutang_pasien.sisapiutang',
                    'piutang_pasien.tgltempo',
                    'penjab.kd_pj',
                    'penjab.png_jawab',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage);
    }

    public function getDataPenjaminProperty(): array
    {
        return Penjamin::query()
            ->where('status', '1')
            ->pluck('png_jawab', 'kd_pj')
            ->all();
    }

    public function render(): View
    {
        return view('livewire.keuangan.piutang-belum-lunas')
            ->layout(BaseLayout::class, ['title' => 'Piutang Belum Lunas']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->sortColumns = [];
        $this->penjamin = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
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
