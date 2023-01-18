<?php

namespace App\Support\Traits\Livewire;

trait Filterable
{
    abstract protected function defaultValues();

    public function initializeFilterable()
    {
        $this->listeners = array_merge($this->listeners, [
            'searchData',
            'resetFilters',
            'fullRefresh',
        ]);
    }

    public function pageName()
    {
        return 'page';
    }

    public function searchData()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage($this->pageName());
        }

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->defaultValues();

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}