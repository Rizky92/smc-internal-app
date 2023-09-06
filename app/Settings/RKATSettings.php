<?php

namespace App\Settings;

use Carbon\Carbon;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;

class RKATSettings extends Settings
{
    public int $tahun;

    public Carbon $tgl_penetapan_awal;

    public Carbon $tgl_penetapan_akhir;
    
    public static function group(): string
    {
        return 'rkat';
    }

    public static function casts(): array
    {
        return [
            'tgl_penetapan_awal'  => DateTimeInterfaceCast::class,
            'tgl_penetapan_akhir' => DateTimeInterfaceCast::class,
        ];
    }
}