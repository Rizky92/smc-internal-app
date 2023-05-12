<?php

namespace App\Support\Traits\Livewire;

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

    public function pageName(): string
    {
        return 'page';
    }

    public function searchData(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage($this->pageName());
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