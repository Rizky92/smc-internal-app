<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NPWPSettings extends Settings
{
    public string $npwp_penjual;

    public static function group(): string
    {
        return 'faktur_pajak';
    }
}
