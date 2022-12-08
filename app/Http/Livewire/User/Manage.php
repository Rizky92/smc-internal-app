<?php

namespace App\Http\Livewire\User;

use App\Role;
use App\User;
use Livewire\Component;
use Livewire\WithPagination;

class Manage extends Component
{
    use WithPagination;

    public $cari;

    public $perpage;

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
        $this->perpage = 25;
    }

    public function getUsersProperty()
    {
        return User::denganPencarian($this->cari)->paginate($this->perpage);
    }

    public function getRolesProperty()
    {
        return Role::pluck('name', 'id');
    }

    public function render()
    {
        return view('livewire.user.manage')
            ->extends('layouts.admin', ['title' => 'Manajemen Hak Akses User'])
            ->section('content');
    }

    public function simpan($nrp, $roles)
    {
        User::findByNRP($nrp)->syncRoles($roles);

        session()->flash('saved.content', "Hak akses untuk user {$nrp} berhasil diubah!");
        session()->flash('saved.type', 'success');
    }

    public function hardRefresh()
    {
        $this->forgetComputed();

        $this->emit('$refresh');
    }
}
