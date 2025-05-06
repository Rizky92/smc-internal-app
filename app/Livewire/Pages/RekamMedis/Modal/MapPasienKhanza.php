<?php

namespace App\Livewire\Pages\RekamMedis\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\LiveTable;
use App\Models\RekamMedis\EpasienUser;
use App\Models\RekamMedis\Pasien;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class MapPasienKhanza extends Component
{
    use DeferredModal;
    use Filterable;
    use LiveTable;

    public $noRkmMedis;

    /** @var string */
    public $noKtp;

    /** @var string */
    public $tglLahir;

    /** @var string */
    public $name;

    /** @var mixed */
    protected $listeners = [
        'epasien.show-map-pasien' => 'showModal',
        'epasien.hide-map-pasien' => 'hideModal',
        'epasien.prepare-set' => 'prepareUser',
        'epasien.set' => 'save',
    ];

    protected function queryString(): array
    {
        return [
            'cari' => ['except' => '', 'as' => 'q'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPasienProperty()
    {
        return $this->isDeferred ? [] : Pasien::query()
            ->search($this->cari, ['no_rkm_medis', 'nm_pasien'])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.modal.map-pasien-khanza');
    }

    public function prepareUser(string $noKtp = '', string $name = '', string $tglLahir = '', $noRkmMedis = ''): void
    {
        $this->noKtp = $noKtp;
        $this->name = $name;
        $this->tglLahir = $tglLahir;
        $this->noRkmMedis = $noRkmMedis;
    }

    protected function defaultValues(): void
    {
        $this->undefer();

        $this->noKtp = '';
        $this->name = '';
        $this->tglLahir = '';
        $this->noRkmMedis = '';
        $this->cari = '';
    }
}
