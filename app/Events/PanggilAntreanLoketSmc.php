<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PanggilAntreanLoketSmc implements ShouldBroadcast
{
    public string $loket;

    public string $antrian;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $loket, string $antrian)
    {
        $this->loket = $loket;
        $this->antrian = $antrian;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('antrean-loket-smc');
    }

    public function broadcastAs(): string
    {
        return 'PanggilAntreanLoketSmc';
    }
}
