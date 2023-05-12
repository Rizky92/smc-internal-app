<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ManajemenUser extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker, DeferredLoading;

    /** @var bool */
    public $tampilkanYangMemilikiHakAkses;

    /** @var \Illuminate\Support\Collection|array */
    public $selectUsers;

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
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return $this->isDeferred
            ? []
            : User::query()
                ->tampilkanYangMemilikiHakAkses($this->tampilkanYangMemilikiHakAkses)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns, [
                    'jbtn' => DB::raw("coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn)"),
                    'jenis' => DB::raw("(case when petugas.nip is not null then 'Petugas' when dokter.kd_dokter is not null then 'Dokter' else '-' end)"),
                ])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen User']);
    }

    protected function defaultValues(): void
    {
        $this->tampilkanYangMemilikiHakAkses = false;
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    public function selectAllUsers(bool $state): void
    {
        if (! $state) {
            $this->selectUsers = [];

            return;
        }

        $this->selectUsers = User::query()
            ->select([DB::raw("aes_decrypt(id_user, 'nur') as user_id")])
            ->pluck('user_id')
            ->flip()
            ->map(fn (int $_) => true)
            ->all();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function impersonateAsUser(string $nrp = '')
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak memiliki izin untuk melakukan tindakan ini!');

            return;
        }

        if (empty($nrp)) {
            $this->flashError('Silahkan pilih user yang ingin diimpersonasikan!');

            return;
        }

        auth()->user()->impersonate(User::findByNRP($nrp));

        return redirect('admin/')
            ->with('flash.type', 'dark')
            ->with('flash.message', "Anda sekarang sedang login sebagai {$nrp}");
    }

    /**
     * @template TA of \App\Models\Aplikasi\Role|string|int
     * @template TB of \App\Models\Aplikasi\Permission|string|int
     * 
     * @param  string|null $nrp
     * @param  string|null $nama
     * @param  array<int, TA>|string|null $roles
     * @param  array<int, TB>|string|null $permissions
     */
    public function prepareUser($nrp, $nama, $roles, $permissions): void
    {
        $this->emitTo('user.khanza.set-hak-akses', 'khanza.prepare-set', $nrp, $nama);
        $this->emitTo('user.khanza.transfer-hak-akses', 'khanza.prepare-transfer', $nrp, $nama);

        $this->emitTo('user.siap.lihat-aktivitas', 'siap.prepare-la', $nrp, $nama);
        $this->emitTo('user.siap.set-perizinan', 'siap.prepare-set', $nrp, $nama, $roles, $permissions);
        $this->emitTo('user.siap.transfer-perizinan', 'siap.prepare-transfer', $nrp, $nama);
    }
}
