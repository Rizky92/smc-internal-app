<?php

namespace Tests\Feature\Livewire\Antrean;

use App\Livewire\Pages\Antrean\AntreanPerPintu;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public waiting-room display board (no route middleware), smoke-tested.
 */
class AntreanPerPintuTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(AntreanPerPintu::class, ['kd_pintu' => 'UJI-PINTU'])->assertOk();
    }
}
