<?php

namespace App\Models\Laboratorium\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait StatusOrder
{
    public function statusOrder(): Attribute
    {
        return Attribute::get(fn ($_, array $attributes): string => 
            is_null($attributes['tgl_hasil']) && is_null($attributes['jam_hasil'])
                ? 'Belum Dilayani'
                : 'Sudah Dilayani'
            );
    }
}