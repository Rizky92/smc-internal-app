<?php

namespace App\Models\Laboratorium\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait StatusOrder
{
    public function statusOrder(): Attribute
    {
        return Attribute::get(fn ($_, array $attributes): string => 
            ($attributes['tgl_hasil'] === '0000-00-00' && $attributes['jam_hasil'] === '00:00:00')
                ? 'Belum Dilayani'
                : 'Sudah Dilayani'
        );
    }
}