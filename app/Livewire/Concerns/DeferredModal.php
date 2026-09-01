<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\On;

trait DeferredModal
{
    use DeferredLoading;

    public function mountDeferredModal(): void
    {
        //
    }

    #[On('showModal')]
    public function showModal(): void
    {
        $this->loadProperties();

        $this->dispatch('modal-loaded');
    }

    #[On('hideModal')]
    public function hideModal(): void
    {
        $this->undefer();

        if (method_exists($this, 'resetFilters')) {
            $this->resetFilters();
        }

        $this->dispatch('modal-unloaded');
    }
}
