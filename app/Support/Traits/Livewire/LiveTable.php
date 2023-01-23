<?php

namespace App\Support\Traits\Livewire;

use Livewire\WithPagination;

trait LiveTable
{
    use WithPagination;

    public $cari;

    public $perpage;

    public $sortBy;

    public $sortDirection;

    protected $paginationTheme = 'bootstrap';

    protected function queryStringLiveTable()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'sortBy' => ['except' => '', 'as' => 'sort'],
            'sortDirection' => ['except' => 'asc', 'as' => 'dir'],
        ];
    }

    public function mountLiveTable()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortBy = collect();
    }

    public function setSortByColumn(string $column, $direction = 'asc')
    {
        $this->sortBy = $column;
        $this->sortDirection = $direction;
    }

    protected function performSort()
    {
        if (method_exists($this, 'searchData')) {
            $this->searchData();
        } else {
            $this->emit('$refresh');
        }
    }
}