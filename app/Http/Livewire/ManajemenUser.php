<?php

namespace App\Http\Livewire;

use App\Role;
use App\User;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination;

    public $cari;

    public $userId;

    public $nama;

    public $hakAkses;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

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
        $this->perpage = 25;
    }

    public function render()
    {
        $users = User::paginate($this->perpage);

        $roles = Role::pluck('name', 'id');

        return view('livewire.manajemen-user', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
