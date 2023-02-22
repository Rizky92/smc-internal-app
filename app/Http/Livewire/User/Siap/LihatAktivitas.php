<?php

namespace App\Http\Livewire\User\Siap;

use App\Support\Traits\Livewire\Filterable;
use Livewire\Component;

class LihatAktivitas extends Component
{
    use Filterable;

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.siap.lihat-aktivitas');
    }

    protected function defaultValues()
    {
        
    }
}
