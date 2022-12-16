<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use Livewire\Component;
use Livewire\WithPagination;

class SetHakAkses extends Component
{
    use WithPagination;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->perpage = 25;
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->paginate($this->perpage);
    }

    public function getPermissionsProperty()
    {
        return Permission::get();
    }

    public function render()
    {
        return view('livewire.user.set-hak-akses')
            ->extends('layouts.admin', ['title' => 'Pengaturan Hak Akses'])
            ->section('content');
    }
}
