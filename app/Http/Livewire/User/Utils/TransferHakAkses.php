<?php

namespace App\Http\Livewire\User\Utils;

use App\Models\Aplikasi\User;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $cari;

    public $currentUser;

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
        ];
    }

    protected $listeners = [
        //
    ];

    public function mount()
    {
        $this->cari = '';
        $this->currentUser = null;
    }

    public function getAvailableUsersProperty()
    {
        return User::all();
    }

    public function render()
    {
        return view('livewire.user.utils.transfer-hak-akses');
    }

    public function setUser(string $nrp)
    {
        $this->currentUser = User::findByNRP($nrp);
    }
}
