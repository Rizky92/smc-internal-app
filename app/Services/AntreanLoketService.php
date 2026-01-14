<?php

namespace App\Services;

use App\Events\PanggilAntreanLoketSmc;
use App\Events\StopAntreanLoketSmc;

class AntreanLoketService
{
    public function call(array $data): void
    {
        event(new PanggilAntreanLoketSmc($data['loket'], $data['nomor']));
    }

    public function stop(): void
    {
        event(new StopAntreanLoketSmc);
    }
}
