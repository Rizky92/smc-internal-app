<?php

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

class BPJS extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bpjs';
    }
}