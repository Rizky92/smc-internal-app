<?php

namespace App\Support\Livewire\Concerns;

trait Filterable
{
    abstract protected function defaultValues(): void;

    public function initializeFilterable(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'searchData',
            'resetState',
            'resetFilters',
            'fullRefresh',
        ]);
    }

    public function searchData(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        $this->emit('$refresh');
    }

    public function resetState(): void
    {
        $this->defaultValues();

        $this->emit('$refresh');
    }

    public function resetFilters(): void
    {
        $this->defaultValues();

        $this->searchData();
    }

    public function fullRefresh(): void
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
