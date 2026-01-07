<?php

namespace Tests\Unit\Services;

use App\Events\PanggilAntreanLoketSmc;
use App\Events\StopAntreanLoketSmc;
use App\Services\AntreanLoketService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AntreanLoketServiceTest extends TestCase
{
    /** @test */
    public function it_dispatches_panggil_antrean_event_with_correct_payload(): void
    {
        Event::fake();

        $service = new AntreanLoketService();

        $payload = [
            'loket' => '01',
            'nomor' => 'A001',
        ];

        $service->call($payload);

        Event::assertDispatched(PanggilAntreanLoketSmc::class, function ($event) {
            return $event->loket === '01'
                && $event->nomor === 'A001';
        });
    }

    /** @test */
    public function it_dispatches_stop_antrean_event(): void
    {
        Event::fake();

        $service = new AntreanLoketService();

        $service->stop();

        Event::assertDispatched(StopAntreanLoketSmc::class);
    }
}
