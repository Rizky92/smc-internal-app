<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The printable posting-jurnal sheet.
 *
 * Its journal data arrives as a JSON string in the query, which means the
 * controller has to cope with whatever a URL can carry: the parameter missing
 * altogether, repeated so PHP hands back an array, or simply not being JSON.
 *
 * RouteSweepTest excludes this route, which is why the crash survived: reaching
 * it without the parameter is exactly the failing case.
 */
class PrintLayoutTest extends TestCase
{
    private const URI = '/print-layout';

    /**
     * @test
     *
     * @dataProvider malformedInput
     */
    public function renders_whatever_the_query_string_carries(string $query): void
    {
        $this->withoutExceptionHandling();

        $this->get(self::URI.$query)->assertOk();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function malformedInput(): array
    {
        return [
            'parameter absent'      => [''],
            'parameter empty'       => ['?jurnalSementara='],
            'not json at all'       => ['?jurnalSementara=bukan-json'],
            'json but not an array' => ['?jurnalSementara='.urlencode('"sebuah string"')],
            'repeated parameter'    => ['?jurnalSementara[]=satu&jurnalSementara[]=dua'],
        ];
    }

    /**
     * @test
     *
     * The shape print-layout.blade.php actually reads.
     */
    public function renders_the_journal_it_is_given(): void
    {
        $payload = json_encode([[
            'jurnal' => [
                'no_jurnal'  => 'UJI-001',
                'no_bukti'   => 'BUKTI-001',
                'tgl_jurnal' => '2026-03-01',
                'jam_jurnal' => '09:00:00',
                'jenis'      => 'U',
                'keterangan' => 'Jurnal Uji',
            ],
            'details' => [
                ['kd_rek' => '1.1.1', 'debet' => 150000, 'kredit' => 0],
            ],
        ]]);

        $this->withoutExceptionHandling();

        $this->get(self::URI.'?jurnalSementara='.urlencode($payload))
            ->assertOk()
            ->assertSee('UJI-001')
            ->assertSee('BUKTI-001');
    }
}
