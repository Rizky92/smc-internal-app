<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class HakAksesCustomReport extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'permission.updated' => 'updatePermissions',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')
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

    public function updatePermissions(int $roleId, array $permissionIds)
    {
        $role = Role::find($roleId);

        $role->syncPermissions($permissionIds);

        $this->flashSuccess('Hak akses berhasil diupdate');
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
