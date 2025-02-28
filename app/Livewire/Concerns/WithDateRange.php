<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

trait WithDateRange
{
    /** @var Carbon|string */
    public $tglAwal;

    /** @var Carbon|string */
    public $tglAkhir;

    protected function queryStringWithDateRange(): array
    {
        return [
            'tglAwal'  => ['except' => $this->firstDateValue()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => $this->lastDateValue()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mountWithDateRange(): void
    {
        $this->defaultValueWithDateRange();
    }

    protected function defaultValuesWithDateRange(): void
    {
        $this->tglAwal = $this->firstDateValue()->toDateString();
        $this->tglAkhir = $this->lastDateValue()->toDateString();
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
