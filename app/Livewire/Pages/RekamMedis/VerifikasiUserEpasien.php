<?php

namespace App\Livewire\Pages\RekamMedis;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\RekamMedis\EpasienUser;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class VerifikasiUserEpasien extends Component
{
    use FlashComponent;
    use Filterable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    protected $listeners = [
        'user.prepare' => 'prepareUser',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : EpasienUser::query()
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.verifikasi-user-epasien')
            ->layout(BaseLayout::class, ['title' => 'Verifikasi User Epasien']);
    }

    protected function defaultValues(): void
    {
        //
    }

    public function prepareUser($noKtp, $name, $tglLahir, $noRkmMedis): void
    {
        $this->emitTo('pages.rekam-medis.modal.map-pasien-khanza', 'epasien.prepare-set', $noKtp, $name, $tglLahir, $noRkmMedis);
    }
}
