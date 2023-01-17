<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent, Searchable;

    public $perpage;

    public $cari;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
    ];

    protected function queryString(): array
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'page' => [
                'except' => 1,
            ],
            'perpage' => [
                'except' => 25,
            ],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->search($this->cari)
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
