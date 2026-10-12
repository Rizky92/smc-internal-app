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

    /**
     * Order matters here. resetFilters() ends in Filterable::searchData(), which
     * lifts the deferral, so undefer() has to come after it — the other way
     * round the modal stays loaded and re-runs its query on every later render
     * of the page it sits on.
     */
    #[On('hideModal')]
    public function hideModal(): void
    {
        if (method_exists($this, 'resetFilters')) {
            $this->resetFilters();
        }

        $this->undefer();

        $this->dispatch('modal-unloaded');
    }
}
