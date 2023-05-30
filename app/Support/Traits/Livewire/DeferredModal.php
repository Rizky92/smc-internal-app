<?php

namespace App\Support\Traits\Livewire;

trait DeferredModal
{
    use DeferredLoading;

    public function mountDeferredModal(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'showModal',
            'hideModal',
        ]);
    }

    public function showModal(): void
    {
        $this->loadProperties();
    }

    public function hideModal(): void
    {
        $this->undefer();

        if (method_exists($this, 'defaultValues')) {
            $this->defaultValues();
        }
    }
}