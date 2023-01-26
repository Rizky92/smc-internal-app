<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable;

    protected function queryString(): array
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->search(Str::lower($this->cari))
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen User']);
    }

    protected function defaultValues()
    {
        $this->perpage = 25;
        $this->cari = '';        
    }
}
