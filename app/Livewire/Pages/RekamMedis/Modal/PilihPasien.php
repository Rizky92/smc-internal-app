<?php

namespace App\Livewire\Pages\RekamMedis\Modal;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\RekamMedis\Pasien;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class PilihPasien extends Component
{
    use DeferredModal;
    use Filterable;
    use LiveTable;

    protected function queryString(): array
    {
        return [
            // 'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            // 'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    protected $listeners = [
        'epasien.show-pilih-pasien' => 'showModal',
        'epasien.hide-pilih-pasien' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : Pasien::query()
            ->search($this->cari, ['no_rkm_medis', 'nm_pasien'])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.modal.pilih-pasien');
    }

    protected function defaultValues(): void
    {
        //
    }
}
