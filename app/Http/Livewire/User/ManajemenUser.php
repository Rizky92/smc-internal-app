<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination;

    public $perpage;

    public $cari;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'hardRefresh',
    ];

    protected function queryString()
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
            ->denganPencarian($this->cari)
            ->paginate($this->perpage);
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function render()
    {
        return view('livewire.user.manajemen-user')
            ->extends('layouts.admin', ['title' => 'Manajemen Hak Akses User'])
            ->section('content');
    }

    public function searchUsers()
    {
        $this->page = 1;

        $this->emit('$refresh');
    }

    public function simpan($nrp, $roles)
    {
        $user = User::findByNRP($nrp);

        $user->syncRoles($roles);

        session()->flash('saved.content', "Hak akses untuk user {$nrp} berhasil diubah!");
        session()->flash('saved.type', 'success');
    }

    public function hardRefresh()
    {
        $this->forgetComputed();

        $this->cari = '';
        $this->perpage = 25;
        $this->page = 1;
        $this->user = null;

        $this->emit('$refresh');
    }
}
