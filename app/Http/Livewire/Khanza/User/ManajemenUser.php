<?php

namespace App\Http\Livewire\Khanza\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
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
        return view('livewire.khanza.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Set Akses User Khanza']);
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->defaultValues();

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }

    private function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
    }
}
