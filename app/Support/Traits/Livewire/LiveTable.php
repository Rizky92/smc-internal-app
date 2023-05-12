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

    /** @var string */
    protected $paginationTheme = 'bootstrap';

    /** @var array|mixed */
    protected $queryStringLiveTable = [
        'cari' => ['except' => ''],
        'perpage' => ['except' => 25],
        'sortColumns' => ['except' => '', 'as' => 'sort'],
    ];

    public function initializeLiveTable(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'sortBy',
            'performSort',
        ]);
    }

    public function mountLiveTable(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
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

        $this->performSort();
    }

    protected function performSort(): void
    {
        $this->emit('$refresh');
    }
}
