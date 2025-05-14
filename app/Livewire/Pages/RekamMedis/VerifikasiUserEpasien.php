<?php

namespace App\Livewire\Pages\RekamMedis;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\User;
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

    /** @var string */
    public $verifiedBy;

    /** @var string */
    public $verifiedByName;

    /** @var bool */
    public $semuaUser;

    protected function queryString(): array
    {
        return [
            'semuaUser' => ['except' => false, 'as' => 'semua'],
        ];
    }

    protected $listeners = [
        'user.prepare' => 'prepareUser',
        'epasien.pilihPasien' => 'setPasien',
    ];

    public function setPasien($noRkmMedis): void
    {
        $this->noRkmMedis = $noRkmMedis;

        $this->dispatchBrowserEvent('data-updated', [
            'noRkmMedis' => $noRkmMedis,
        ]);
    }

    public function mount(): void
    {
        $this->defaultValues();
        $this->verifiedBy = auth()->user()->nik;
        $this->verifiedByName = auth()->user()->nama;
    }

    public function getPetugasProperty()
    {
        return User::pluck('nik', 'nama')->all();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : EpasienUser::query()
            ->verifikasiPasien($this->semuaUser)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.verifikasi-user-epasien')
            ->layout(BaseLayout::class, ['title' => 'Verifikasi User Epasien']);
    }

    public function simpan(int $userId, string $noRkmMedis, string $verifiedBy)
    {
        if (user()->cannot('rekam-medis.epasien.verifikasi')) {
            $this->flashError('Anda tidak memiliki izin untuk memverifikasi user e-pasien');

            return;
        }

        tracker_start('mysql_epasien');

        EpasienUser::updateOrCreate([
            'id'     => $userId,
        ], [
            'no_rkm_medis' => $noRkmMedis,
            'no_rkm_medis_verified_by'  => $verifiedBy,
        ]);

        tracker_end('mysql_epasien');

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-tersimpan');
        $this->flashSuccess('Data berhasil disimpan!');
    }

    protected function defaultValues(): void
    {
        $this->semuaUser = false;
    }
}
