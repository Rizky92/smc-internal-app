<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Antrean\AntreanDiPanggil;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The queue-calling display board, one per entrance.
 *
 * Its polling method used to be named call(), which sounds harmless but is
 * reserved on Livewire 3's $wire proxy — an alias for $wire's own internal
 * $call() helper (see WireShorthandReservedWordsTest for the full list and
 * the mechanism). wire:poll="call" never reached this method: it invoked
 * Livewire's helper with no arguments instead, which POSTs
 * {method: "undefined"} to the server on every tick. The result was a
 * screen that 500'd every two seconds while sitting idle on a wall-mounted
 * display — a JS-runtime bug invisible to Livewire::test(), which calls PHP
 * methods directly and never goes through the $wire proxy that broke.
 *
 * The method is now panggilAntrean(); this pins the rename and the
 * behaviour it's supposed to have (renders, and is a no-op re-entry guard
 * while a patient is already being called).
 */
class AntreanDiPanggilTest extends TestCase
{
    /**
     * @test
     */
    public function keeps_the_poll_directive_pointed_at_a_real_method(): void
    {
        $test = Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => 'TIDAKADA']);

        $test->assertSee('wire:poll.2000ms.keep-alive="panggilAntrean"', false);
    }

    /**
     * @test
     */
    public function does_nothing_while_a_patient_is_already_being_called(): void
    {
        $test = Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => 'TIDAKADA']);

        $test->set('isCalling', true);
        $test->set('antreanDipanggilSekarang', ['no_rawat' => 'UJI/1']);

        $test->call('panggilAntrean');

        $test->assertSet('antreanDipanggilSekarang', ['no_rawat' => 'UJI/1']);
    }
}
