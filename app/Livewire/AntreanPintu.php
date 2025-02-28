<?php

namespace App\Livewire;

use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class AntreanPintu extends Component
{
    /**
     * @psalm-return Collection<Pintu>
     */
    public function getPintuProperty(): Collection
    {
        return Pintu::all();
    }

    public function render(): View
    {
        return view('livewire.antrean-pintu');
    }
}
