<?php

namespace App\Http\Livewire\HakAkses\Siap;

use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\DeferredModal;
use Livewire\Component;

class ModalPerizinanBaru extends Component
{
    use DeferredModal;

    public $roleName = null;

    protected $listeners = [
        'siap.show-mpb' => 'showModal',
        'siap.hide-mpb' => 'hideModal',
        'siap.new' => 'newRole',
    ];

    protected $rules = [
        'roleName' => ['required', 'string', 'max:255'],
    ];

    public function render()
    {
        return view('livewire.hak-akses.siap.modal-perizinan-baru');
    }

    public function newRole()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitUp('flash.error');

            return;
        }

        tracker_start('mysql_smc');

        $role = Role::create(['name' => $this->roleName, 'guard_name' => 'web']);

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', "Role {$role->name} berhasil dibuat!");

        $this->roleName = null;
    }
}
