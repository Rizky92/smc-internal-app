<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AntreanPintu;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public landing page listing every "pintu" (queue door) - no route
 * middleware, no filtering. Smoke-tested.
 */
class AntreanPintuTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(AntreanPintu::class)->assertOk();
    }
}
