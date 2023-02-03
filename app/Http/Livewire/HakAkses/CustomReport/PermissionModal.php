<?php

namespace App\Http\Livewire\HakAkses\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\Modal;
use Illuminate\Support\Str;
use Livewire\Component;

class PermissionModal extends Component
{
    use Filterable, LiveTable, Modal;

    /** @var int $roleId */
    public $roleId;

    /** @var array<int, int> $permissionIds */
    public $permissionIds;

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.hak-akses.custom-report.permission-modal');
    }

    public function getPermissionsProperty()
    {
        return $this->isDeferred
            ? []
            : Permission::query()
            ->search($this->cari, ['name'])
            ->pluck('name', 'id')
            ->map(fn ($name, $id) => compact('name', 'id'))
            ->mapToGroups(fn ($item) => [Str::before($item['name'], '.') => $item])
            ->map(fn ($item) => collect($item)->mapWithKeys(fn ($value) => [$value['id'] => $value['name']]))
            ->toArray();
    }

    protected function defaultValues()
    {
        $this->isDeferred = true;
        $this->cari = '';
        $this->roleId = -1;
        $this->permissionIds = [];
    }
}
