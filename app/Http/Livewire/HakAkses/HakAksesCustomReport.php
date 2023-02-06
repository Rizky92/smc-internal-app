<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class HakAksesCustomReport extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable;

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
        return Permission::orderBy('name')->pluck('name', 'id');
    }

    public function render()
    {
        return view('livewire.hak-akses.hak-akses-custom-report')
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