<?php

namespace App\Http\Livewire\HakAkses;

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

    public function mount()
    {
        $this->defaultValues();
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->paginate($this->perpage);
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
}
