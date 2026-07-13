<?php

namespace App\Livewire\Pages\RekamMedis\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\LiveTable;
use App\Models\RekamMedis\Pasien;
use Illuminate\View\View;
use Livewire\Component;

class PilihPasien extends Component
{
    use DeferredModal;
    use Filterable;
    use LiveTable;

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
