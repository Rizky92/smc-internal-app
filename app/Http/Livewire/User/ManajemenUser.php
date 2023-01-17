<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent;

    public $perpage;

    public $cari;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
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
        $this->cari = '';
        $this->perpage = 25;
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with(['roles.permissions', 'permissions'])
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen User']);
    }

    public function setUser(string $nrp)
    {
        $this->currentUser = User::findByNRP($nrp);
    }

    public function clearUser()
    {
        $this->currentUser = null;
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
