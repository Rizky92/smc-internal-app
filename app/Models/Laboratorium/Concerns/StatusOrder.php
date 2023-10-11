<?php

namespace App\Models\Laboratorium\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait StatusOrder
{
    protected function dateAttributeName(): string
    {
        return 'tgl_hasil';
    }

    protected function timeAttributeName(): string
    {
        return 'jam_hasil';
    }

    public function statusOrder(): Attribute
    {
        return Attribute::get(function ($_, array $attributes): string {
            $tgl = $attributes[$this->dateAttributeName()];
            $jam = $attributes[$this->timeAttributeName()];

            if ($tgl === '0000-00-00' && $jam = '00:00:00') {
                return 'Belum Dilayani';
            }

            return 'Sudah Dilayani';
        });
    }
}
