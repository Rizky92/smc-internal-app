<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\HakAkses;
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

class Siap extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

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

    public function render()
    {
        return view('livewire.hak-akses.siap')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan perizinan SMC Internal App']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }
}
