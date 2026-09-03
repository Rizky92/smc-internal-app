<?php

namespace App\Livewire\Pages\Aplikasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\Pintu;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class ManajemenPintu extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected function queryString(): array
    {
        return [
            'cari'    => ['except' => ''],
            'perpage' => ['except' => 25],
        ];
    }

    /**
     * @return Paginator|array
     */
    public function getPintuProperty()
    {
        if ($this->isDeferred) {
            return [];
        }

        $pintus = Pintu::query()
            ->with(['dokter', 'poliklinik'])
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);

        $jadwalRows = DB::connection('mysql_sik')
            ->table('set_pintu_smc')
            ->join('dokter', 'set_pintu_smc.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'set_pintu_smc.kd_poli', '=', 'poliklinik.kd_poli')
            ->select(
                'set_pintu_smc.kd_pintu',
                'set_pintu_smc.kd_dokter',
                'set_pintu_smc.kd_poli',
                'dokter.nm_dokter',
                'poliklinik.nm_poli'
            )
            ->distinct()
            ->orderBy('set_pintu_smc.kd_pintu')
            ->orderBy('dokter.nm_dokter')
            ->get();

        $jadwalMap = $jadwalRows->groupBy('kd_pintu');

        $pintus->each(function ($pintu) use ($jadwalMap) {
            $collection = $jadwalMap->get($pintu->kd_pintu, collect())->map(fn ($r) => (object) [
                'kd_dokter' => $r->kd_dokter,
                'kd_poli'   => $r->kd_poli,
                'nm_dokter' => $r->nm_dokter,
                'nm_poli'   => $r->nm_poli,
            ]);

            $pintu->jadwal = $collection;
        });

        return $pintus;
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.manajemen-pintu')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Pintu']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
