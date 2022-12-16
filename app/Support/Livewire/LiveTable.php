<?php

namespace App\Support\Livewire;

trait LiveTable
{
    public $search;

    public $perpage;

    public $dateStart;

    public $dateEnd;

    protected $paginationTheme = 'bootstrap';

    public function initializeLiveTable()
    {
        $this->listeners = array_merge($this->listeners, [
            'flash',
        ]);
    }

    public function searchData()
    {
        if (method_exists($this, 'gotoPage')) {
            $this->gotoPage(1);
        }

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        if (method_exists($this, 'gotoPage')) {
            $this->gotoPage(1);
        }

        $this->fill([
            'search' => '',
            'dateStart' => now()->startOfMonth()->format('Y-m-d'),
            'dateEnd' => now()->endOfMonth()->format('Y-m-d'),
            'perpage' => 25,
        ]);

        $this->emit('$refresh');
    }

    public function resetFiltersAndHardRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}