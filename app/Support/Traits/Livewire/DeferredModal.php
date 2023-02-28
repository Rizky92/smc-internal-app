<?php

namespace App\Support\Traits\Livewire;

trait DeferredModal
{
    use DeferredLoading;

    public function mountDeferredModal()
    {
        $this->listeners = array_merge($this->listeners, [
            'showModal',
            'hideModal',
        ]);
    }

    public function showModal()
    {
        $this->loadProperties();
    }

    public function hideModal()
    {
        $this->undefer();
    }
}