<?php

namespace App\Support\Traits\Livewire;

use Livewire\WithPagination;

trait LiveTable
{
    use WithPagination;

    /** @var string */
    public $cari;

    /** @var int */
    public $perpage;

    /** @var string[] */
    public $sortColumns;

    /** @var string */
    protected $paginationTheme = 'bootstrap';

    /** @var array|mixed */
    protected $queryStringLiveTable = [
        'cari'        => ['except' => ''],
        'perpage'     => ['except' => 25],
    ];

    public function initializeLiveTable(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'sortBy',
        ]);
    }

    public function mountLiveTable(): void
    {
        $this->defaultValuesLiveTable();
    }

    public function sortBy(string $column, ?string $direction): void
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

        $this->emit('$refresh');
    }

    protected function defaultValuesLiveTable(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }
}
