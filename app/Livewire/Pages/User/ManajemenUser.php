<?php

namespace App\Livewire\Pages\User;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\User;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class ManajemenUser extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var bool */
    public $tampilkanYangMemilikiHakAkses;

    /** @var mixed */
    protected $listeners = [
        'user.prepare' => 'prepareUser',
    ];

    protected function queryString(): array
    {
        return [
            'tampilkanYangMemilikiHakAkses' => ['except' => false, 'as' => 'hak_akses'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array|Paginator
     */
    public function getUsersProperty()
    {
        return $this->isDeferred
            ? []
            : User::query()
                ->with([
                    'roles.permissions',
                    'permissions',
                ])
                ->tampilkanYangMemilikiHakAkses($this->tampilkanYangMemilikiHakAkses)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns, [
                    'jbtn'  => DB::raw("coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn, '-')"),
                    'jenis' => DB::raw("(case when petugas.nip is not null then 'Petugas' when dokter.kd_dokter is not null then 'Dokter' else '-' end)"),
                ])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen User']);
    }

    protected function defaultValues(): void
    {
        $this->tampilkanYangMemilikiHakAkses = false;
    }

    /**
     * @return void|RedirectResponse
     */
    public function impersonateAsUser(string $nrp = '')
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak memiliki izin untuk melakukan tindakan ini!');

            return;
        }

        if (empty($nrp)) {
            $this->flashError('Silahkan pilih user yang ingin diimpersonasikan!');

            return;
        }

        user()->impersonate(User::findByNRP($nrp));

        return redirect(route('admin.dashboard'))
            ->with('flash.type', 'dark')
            ->with('flash.message', "Anda sekarang sedang login sebagai {$nrp}");
    }

    /**
     * @param  string|null  $nrp
     * @param  string|null  $nama
     * @param  array<string|int, bool|string>  $roles
     * @param  array<string|int, bool|string>  $permissions
     */
    public function prepareUser($nrp, $nama, $roles, $permissions): void
    {
        $this->emitTo('pages.user.khanza.set-hak-akses', 'khanza.prepare-set', $nrp, $nama);
        $this->emitTo('pages.user.khanza.transfer-hak-akses', 'khanza.prepare-transfer', $nrp, $nama);

        $this->emitTo('pages.user.siap.lihat-aktivitas', 'siap.prepare-la', $nrp, $nama);
        $this->emitTo('pages.user.siap.set-perizinan', 'siap.prepare-set', $nrp, $nama, $roles, $permissions);
        $this->emitTo('pages.user.siap.transfer-perizinan', 'siap.prepare-transfer', $nrp, $nama);
    }
}
