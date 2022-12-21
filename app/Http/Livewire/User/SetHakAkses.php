<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class SetHakAkses extends Component
{
    use WithPagination, FlashComponent;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'permission.updated' => 'updatePermissions',
    ];

    public function mount()
    {
        $this->perpage = 25;
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->paginate($this->perpage);
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->pluck('name', 'id');
    }

    public function render()
    {
        return view('livewire.user.set-hak-akses')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan Hak Akses']);
    }

    /**
     * @param  int $roleId
     * @param  array<int,int> $permissionIds
     * 
     * @return void
     */
    public function updatePermissions(int $roleId, array $permissionIds)
    {
        $role = Role::find($roleId);

        $role->syncPermissions($permissionIds);

        $this->flashSuccess('Hak akses berhasil diupdate');
    }
}
