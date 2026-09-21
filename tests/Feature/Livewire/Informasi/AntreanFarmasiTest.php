<?php

namespace Tests\Feature\Livewire\Informasi;

use App\Livewire\Pages\Informasi\AntreanFarmasi;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A public waiting-room display board with no route middleware and no
 * component logic at all (no properties, no computed data) - the smoke test
 * is the whole of what there is to verify.
 */
class AntreanFarmasiTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(AntreanFarmasi::class)->assertOk();
    }
}
