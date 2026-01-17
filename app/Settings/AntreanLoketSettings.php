<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AntreanLoketSettings extends Settings
{
    public bool $antrean_prefix_huruf;

    public array $prefix_huruf_aktif;

    public const PREFIX_HURUF_OPTIONS = [
        'A' => 'A',
        'B' => 'B',
        'C' => 'C',
        'D' => 'D',
        'E' => 'E',
        'F' => 'F',
    ];

    public static function group(): string
    {
        return 'antrean_loket';
    }
}
