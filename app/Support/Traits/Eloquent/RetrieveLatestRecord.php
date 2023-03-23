<?php

namespace App\Support\Traits\Eloquent;

trait RetrieveLatestRecord
{
    public static function findLatest()
    {
        $data = static::query()
            ->first();
    }
}