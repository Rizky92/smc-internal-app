<?php

namespace App\Livewire\Pages\Antrian;

use Livewire\Component;

class AntrianPoli extends Component
{
    public $nextAntrian;

    public function mount($nextAntrian)
    {
        $this->nextAntrian = $nextAntrian;
    }

    public function render()
    {
        return view('livewire.pages.antrian.antrian-poli');
    }
}