<?php

namespace App\Support\Traits\Livewire;

trait WithDateRange
{
    /** @var \Carbon\Carbon */
    public $tglAwal;

    /** @var \Carbon\Carbon */
    public $tglAkhir;

    protected function queryStringWithDateRange(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mountWithDateRange(): void
    {
        $this->defaultValueWithDateRange();
    }

    public function hydrate(): void
    {
        
    }

    protected function defaultValueWithDateRange(): void
    {
        $this->tglAwal = now()->startOfMonth();
        $this->tglAkhir = now()->endOfMonth();
    }
}