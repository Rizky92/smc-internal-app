<?php

namespace App\Support\Traits\Livewire;

trait Modal
{
    /** @var bool $isDeferred */
    public $isDeferred;

    public function initializeModal()
    {
        $this->listeners = array_merge($this->listeners, [
            'showModal',
            'hideModal',
        ]);
    }

    protected function queryStringModal()
    {
        return [];
    }

    public function mountModal()
    {
        $this->isDeferred = true;
    }

    public function showModal()
    {
        $this->isDeferred = false;

        $this->dispatchBrowserEvent('modal.show');
    }

    public function hideModal()
    {
        $this->isDeferred = true;

        $this->dispatchBrowserEvent('modal.hide');
    }
}