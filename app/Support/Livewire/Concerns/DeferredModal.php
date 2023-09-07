<?php

namespace App\Support\Livewire\Concerns;

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

        $this->dispatchBrowserEvent('modal-loaded');
    }

    public function hideModal(): void
    {
        $this->undefer();

        if (method_exists($this, 'defaultValues')) {
            $this->defaultValues();
        }

        $this->dispatchBrowserEvent('modal-unloaded');
    }
}
