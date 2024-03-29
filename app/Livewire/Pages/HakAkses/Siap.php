<?php

namespace App\Livewire\Pages\HakAkses;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\Role;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class Siap extends Component
{
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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
        return view('livewire.pages.hak-akses.siap')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan perizinan SMC Internal App']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }
}
