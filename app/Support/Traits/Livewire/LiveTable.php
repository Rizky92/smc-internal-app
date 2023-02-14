<?php

namespace App\Support\Traits\Livewire;

use Livewire\WithPagination;

trait LiveTable
{
    use WithPagination;

    /** @var string $cari = '' */
    public $cari;

    /** @var int $perpage = 25 */
    public $perpage;

    /** @var array<string, string> $sortColumns = [] */
    public $sortColumns;

    /** @var bool $isReadyToLoad = true */
    public $isReadyToLoad;

    protected $paginationTheme = 'bootstrap';

    protected $queryStringLiveTable = [
        'cari' => ['except' => ''],
        'perpage' => ['except' => 25],
        'sortColumns' => ['except' => '', 'as' => 'sort'],
    ];

    public function initializeLiveTable()
    {
        $this->listeners = array_merge($this->listeners, [
            'sortBy',
            'performSort',
        ]);
    }

    public function mountLiveTable()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->isReadyToLoad = false;
    }

    public function sortBy(string $column, ?string $direction)
    {
        switch ($direction) {
            case null:
            case '':
                $this->sortColumns = array_merge($this->sortColumns, [$column => 'asc']);
                break;

            case 'asc':
                $this->sortColumns = array_merge($this->sortColumns, [$column => 'desc']);
                break;

            default:
                unset($this->sortColumns[$column]);
                break;
        }

        $this->performSort();
    }

    public function loadProperties()
    {
        $this->isReadyToLoad = true;

        $this->emit('$refresh');
    }

    protected function performSort()
    {
        $this->emit('$refresh');
    }
}
