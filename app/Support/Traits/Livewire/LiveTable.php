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
        ];
    }
}