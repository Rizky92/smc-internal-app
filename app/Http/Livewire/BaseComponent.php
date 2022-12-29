<?php

namespace App\Http\Livewire;

use App\Support\Traits\Livewire\FlashComponent;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseComponent extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'perpage' => [
                'except' => 25,
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir',
            ],
        ];
    }

    abstract public function render();

    abstract public function defaultPropertyValues(): array;
}
