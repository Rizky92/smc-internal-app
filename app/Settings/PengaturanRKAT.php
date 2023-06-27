<?php

namespace App\Settings;

use Carbon\Carbon;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;

class PengaturanRKAT extends Settings
{
    public string $tahun;

    public Carbon $tgl_awal;

    public Carbon $tgl_akhir;
    
    public static function group(): string
    {
        return 'rkat';
    }

    public static function casts(): array
    {
        return [
            'tgl_awal' => DateTimeInterfaceCast::class,
            'tgl_akhir' => DateTimeInterfaceCast::class,
        ];
    }
}