<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Siap extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable, MenuTracker;

    protected $listeners = [
        'permission.updated' => 'updatePermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getRolesProperty()
    {
        return Role::query()
            ->with('permissions')
            ->paginate($this->perpage);
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')
            ->pluck('name', 'id')
            ->groupBy(fn ($permission, $id) => Str::before($permission, '.'), $preserveKeys = true);
    }

    public function render()
    {
        return view('livewire.hak-akses.siap')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan Hak Akses']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    public function updatePermissions(int $roleId, array $permissionIds)
    {
        $role = Role::find($roleId);

        $role->syncPermissions($permissionIds);

        $this->flashSuccess('Hak akses berhasil diupdate');
    }
}