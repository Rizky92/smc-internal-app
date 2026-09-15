<?php

namespace Tests\Feature\Livewire\Informasi\AntreanFarmasi;

use App\Livewire\Pages\Informasi\AntreanFarmasi\ListPenyerahan;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public pharmacy-queue display board, smoke-tested. refreshData() just
 * re-dispatches '$refresh' in response to a JS marquee-finished event; there
 * is nothing to assert about it beyond the component rendering at all.
 */
class ListPenyerahanTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(ListPenyerahan::class)->assertOk();
    }
}
