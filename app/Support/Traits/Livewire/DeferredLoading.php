<?php

namespace App\Support\Traits\Livewire;

trait DefferedLoading
{
    public $isDeffered;

    public function mountDefferedLoading()
    {
        $this->isDeffered = true;
    }

    public function loadProperties()
    {
        $this->isDeffered = false;

        $this->emit('$refresh');
    }
}