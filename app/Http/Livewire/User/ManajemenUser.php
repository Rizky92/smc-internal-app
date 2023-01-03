<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent;

    public $perpage;

    public $cari;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    protected function queryString(): array
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'page' => [
                'except' => 1,
            ],
            'perpage' => [
                'except' => 25,
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with(['roles.permissions', 'permissions'])
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty()
    {
        $availablePermissions = $this->roles->map(function ($role) {
            return $role->permissions->map(function ($permission) {
                return $permission->id;
            });
        })->flatten()->toArray();

        return Permission::whereNotIn('id', $availablePermissions)->get();
    }

    public function render()
    {
        return view('livewire.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Hak Akses User']);
    }

    public function simpan(string $nrp, array $roles, array $permissions)
    {
        $user = User::findByNRP($nrp);

        if ($user->is(auth()->user())) {
            $this->flashError('Tidak dapat mengubah hak akses untuk diri sendiri!');

            return;
        }
        
        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        $this->flashSuccess("Hak akses untuk user {$nrp} berhasil diubah!");
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
