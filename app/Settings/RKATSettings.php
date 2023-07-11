<?php

namespace App\Settings;

use Carbon\Carbon;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;

class RKATSettings extends Settings
{
    public int $tahun;

    public Carbon $batas_penetapan_awal;

    public Carbon $batas_penetapan_akhir;

    public Carbon $batas_input_awal;

    public Carbon $batas_input_akhir;
    
    public static function group(): string
    {
        return 'rkat';
    }

    public static function casts(): array
    {
        return [
            'batas_penetapan_awal'  => DateTimeInterfaceCast::class,
            'batas_penetapan_akhir' => DateTimeInterfaceCast::class,
            'batas_input_awal'      => DateTimeInterfaceCast::class,
            'batas_input_akhir'     => DateTimeInterfaceCast::class,
        ];
    }
}