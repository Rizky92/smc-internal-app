<?php

namespace App\Livewire;

use App\Models\Aplikasi\Pintu;
use Livewire\Component;

class Antrean extends Component
{
    public function getPintuProperty()
    {
        return Pintu::all();
    }

    public function render()
    {
        return view('livewire.antrean');
    }
}
