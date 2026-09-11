<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The public waiting-room display for one poliklinik.
 *
 * Reached at /antrean/{kd_poli}, so the identifier comes straight from the URL
 * and nothing guarantees it names a poli that exists — a clinic can be renamed
 * or retired in Khanza while a display in a corridor is still pointed at the old
 * code. Parameterised routes are excluded from RouteSweepTest, which is why this
 * screen needs its own test.
 */
class AntreanPoliTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_sik')->table('poliklinik')->where('kd_poli', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function createPoliklinik(string $kdPoli, string $nama): void
    {
        DB::connection('mysql_sik')->table('poliklinik')->insert([
            'kd_poli'        => $kdPoli,
            'nm_poli'        => $nama,
            'registrasi'     => 0,
            'registrasilama' => 0,
            'status'         => '1',
        ]);
    }

    /**
     * @test
     */
    public function shows_the_name_of_the_poliklinik(): void
    {
        $this->createPoliklinik('UJI01', 'Poli Uji Dalam');

        $this->withoutExceptionHandling();

        $this->get('/antrean/UJI01')
            ->assertOk()
            ->assertSee('Poli Uji Dalam');
    }

    /**
     * @test
     *
     * An unknown code must not take the display down. The header simply has no
     * name to show.
     */
    public function survives_a_kode_poli_that_does_not_exist(): void
    {
        $this->withoutExceptionHandling();

        $this->get('/antrean/TIDAKADA')->assertOk();
    }
}
