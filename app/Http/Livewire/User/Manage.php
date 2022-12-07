<?php

namespace App\Http\Livewire\User;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        'simpan',
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
        $this->currentNRP = '';
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

    public function simpan(string $nrp, array $roles)
    {
        if (empty($this->currentNRP) || ! $roles) {
            session()->flash('saved.content', "Maaf, terjadi error! Silahkan coba lagi.");
            session()->flash('saved.type', 'danger');
        }

        User::findByNRP($nrp)->syncRoles($roles);

        session()->flash('saved.content', "Hak akses untuk user {$this->currentNRP} berhasil diubah!");
        session()->flash('saved.type', 'success');
    }

    public function hardRefresh()
    {
        $this->forgetComputed();

        $this->emit('$refresh');
    }
}
