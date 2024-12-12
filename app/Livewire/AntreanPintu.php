<?php

namespace App\Livewire;

use App\Models\Aplikasi\Pintu;
use Illuminate\View\View;
use Livewire\Component;

class AntreanPintu extends Component
{
    public function getPintuProperty()
    {
        return Pintu::all();
    }

    public function render(): View
    {
        return view('livewire.antrean-pintu');
    }
}
