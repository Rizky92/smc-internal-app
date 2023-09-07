<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\Role;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class Siap extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getRolesProperty(): Paginator
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
