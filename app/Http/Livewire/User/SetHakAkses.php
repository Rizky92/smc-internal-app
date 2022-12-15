<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    
    public function render()
    {
        return view('livewire.user.set-hak-akses')
            ->extends('layouts.app')
            ->section('content');
    }
}
