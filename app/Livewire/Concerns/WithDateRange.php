<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

trait WithDateRange
{
    /** @var \Carbon\Carbon|string */
    public $tglAwal;

    /** @var \Carbon\Carbon|string */
    public $tglAkhir;

    protected function queryStringWithDateRange(): array
    {
        return [
            'tglAwal'  => ['except' => $this->firstDateValue()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => $this->lastDateValue()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mountWithDateRange(): void
    {
        $this->defaultValueWithDateRange();
    }

    protected function defaultValuesWithDateRange(): void
    {
        $this->tglAwal = $this->firstDateValue()->format('Y-m-d');
        $this->tglAkhir = $this->lastDateValue()->format('Y-m-d');
    }

    protected function firstDateValue(): Carbon
    {
        return now()->startOfMonth();
    }

    protected function lastDateValue(): Carbon
    {
        return now()->endOfMonth();
    }
}
