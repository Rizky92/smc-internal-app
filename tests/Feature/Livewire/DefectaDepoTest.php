<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The pharmacy's defecta page, which picks the current shift from closing_kasir.
 *
 * Khanza defines three shifts: Pagi from 07:00, Siang from 14:00, Malam from
 * 21:00. dataShiftKerja() keeps the shifts whose start hour is at or before the
 * current hour and takes the first, so between midnight and 06:59 nothing
 * matches at all — Malam's window wraps past midnight but the comparison does
 * not follow it.
 *
 * The route sweep only ever asked at the hour it happened to run.
 */
class DefectaDepoTest extends TestCase
{
    private const URI = '/admin/farmasi/defecta-depo';

    protected function setUp(): void
    {
        parent::setUp();

        // dataShiftKerja() caches the shift table for a week.
        Cache::forget('waktu_shift_semua');
    }

    /**
     * @test
     *
     * @dataProvider hoursOfTheDay
     */
    public function opens_at_any_hour(string $time): void
    {
        $petugas = $this->petugasWithPermissions(['farmasi.defecta-depo.read'], '99999901');

        $this->travelTo($time);

        $this->withoutExceptionHandling();

        $this->actingAs($petugas)->get(self::URI)->assertOk();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function hoursOfTheDay(): array
    {
        return [
            'night shift, after midnight'   => ['2026-03-02 03:00:00'],
            'just before the morning shift' => ['2026-03-02 06:59:00'],
            'morning shift'                 => ['2026-03-02 09:00:00'],
            'afternoon shift'               => ['2026-03-02 15:00:00'],
            'night shift, before midnight'  => ['2026-03-02 22:00:00'],
        ];
    }
}
