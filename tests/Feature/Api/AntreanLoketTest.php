<?php

namespace Tests\Feature\Api;

use App\Services\AntreanLoketService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AntreanLoketTest extends TestCase
{
    /** @test */
    public function it_sends_antrean_payload_and_receives_ok_json_response(): void
    {
        $this->instance(AntreanLoketService::class, Mockery::mock(AntreanLoketService::class, function (MockInterface $mock) {
            $mock->shouldReceive('call')
                ->once()
                ->with([
                    'action' => 'called',
                    'loket' => '01',
                    'nomor' => 'A001',
                ]);
        }));

        $response = $this->postJson('/api/panggil-antrean-loket-smc', [
            'action' => 'called',
            'loket' => '01',
            'nomor' => 'A001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'action' => 'called',
                'loket' => '01',
                'nomor' => 'A001',
            ]);
    }

    /** @test */
    public function it_fails_validation_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/panggil-antrean-loket-smc', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }


    /** @test */
    public function it_sends_stop_payload_and_receives_ok_json_response(): void
    {
        $this->instance(AntreanLoketService::class, Mockery::mock(AntreanLoketService::class, function (MockInterface $mock) {
            $mock->shouldReceive('stop')
                ->once();
        }));

        $response = $this->postJson('/api/stop-antrean-loket-smc', [
            'action' => 'stopped',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Antrean loket stopped successfully.',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}