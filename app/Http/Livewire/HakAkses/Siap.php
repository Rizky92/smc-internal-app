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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class Siap extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getRolesProperty(): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.hak-akses.siap')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan perizinan SMC Internal App']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }
}
