<?php

namespace App\Support\Traits\Livewire;

trait LiveTable
{
    public $search;

    public $perpage;

    public $dateStart;

    public $dateEnd;

    protected $paginationTheme = 'bootstrap';

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