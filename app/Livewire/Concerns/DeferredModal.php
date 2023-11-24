<?php

namespace App\Livewire\Concerns;

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

        if (method_exists($this, 'resetFilters')) {
            $this->resetFilters();
        }

        $this->dispatchBrowserEvent('modal-unloaded');
    }
}
