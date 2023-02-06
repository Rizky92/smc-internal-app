<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\RoleAlreadyExists;

class HakAksesCustomReport extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable;

    public $roleId;

    public $roleName;

    public function mount()
    {
        $this->defaultValues();
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')
            ->orderBy('name')
            ->paginate($this->perpage);
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
        $this->roleName = '';
    }

    public function createOrUpdateRole()
    {
        $validator = Validator::make(
            ['name' => $this->roleName],
            ['name' => 'required|string|max:255'],
            ['name.string' => 'Nama role harus berupa string!']
        );

        if ($validator->fails()) {
            $this->flashError($validator->messages()->get('name')[0]);

            return;
        }

        try {
            Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
        } catch (RoleAlreadyExists $e) {
            $this->flashError($e->getMessage());

            return;
        }

        $this->flashSuccess("Role {$this->roleName} berhasil dibuat!");

        $this->resetFilters();
    }

    public function deleteRole()
    {

    }
}
