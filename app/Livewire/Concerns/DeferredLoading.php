<?php

namespace App\Livewire\Concerns;

trait DeferredLoading
{
    /** @var bool */
    public $isDeferred;

    public function mountDeferredLoading(): void
    {
        $this->isDeferred = true;
    }

    public function loadProperties(): void
    {
        $this->isDeferred = false;

        $this->emit('$refresh');
    }

    public function undefer(): void
    {
        $this->isDeferred = true;

        $this->emit('$refresh');
    }
}
