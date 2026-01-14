<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class StopAntreanLoketSmc implements ShouldBroadcast
{
    public string $queue = 'antrean-loket';

    public function broadcastOn()
    {
        return new Channel('antrean-loket-smc');
    }

    public function broadcastAs(): string
    {
        return 'StopAntreanLoketSmc';
    }
}
