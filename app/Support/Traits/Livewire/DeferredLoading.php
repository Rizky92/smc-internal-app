<?php

namespace App\Support\Traits\Livewire;

trait DeferredLoading
{
    public $isDeferred;

    public function mountDeferredLoading()
    {
        $this->isDeferred = true;
    }

    public function loadProperties()
    {
        $this->isDeferred = false;

        $this->emit('$refresh');
    }

    public function resetState()
    {
        $this->isDeferred = true;

        $this->emit('$refresh');
    }
}