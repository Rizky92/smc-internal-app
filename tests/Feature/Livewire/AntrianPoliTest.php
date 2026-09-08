<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;

/**
 * The polling endpoint behind the waiting-room display.
 *
 * livewire/pages/antrian/antrian-poli.blade.php calls this on a setInterval, so
 * it runs continuously for every poli on display — including the ones with
 * nobody waiting. It is a POST, which is why the GET route sweep never reached
 * it.
 */
class AntrianPoliTest extends TestCase
{
    private function endpoint(string $kdPoli = 'UJI01', string $kdDokter = '99999902'): string
    {
        return route('antrian-poli.checkDataChanges', [
            'kd_poli'   => $kdPoli,
            'kd_dokter' => $kdDokter,
        ]);
    }

    /**
     * @test
     *
     * An empty queue is the ordinary state of a poli outside its session, not an
     * error. The display polls regardless.
     */
    public function answers_when_nobody_is_waiting(): void
    {
        $this->postJson($this->endpoint(), ['lastNoReg' => null])
            ->assertOk()
            ->assertJson(['changed' => false, 'data' => null]);
    }
}
