<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SidebarMenu extends Component
{
    public $currentRoute;

    public $cari;

    public function mount()
    {
        
    }

    public function getMenuRoutesProperty()
    {
        
    }

    public function render()
    {
        return view('livewire.sidebar-menu');
    }
}
