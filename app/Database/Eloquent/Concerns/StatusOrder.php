<?php

namespace App\Database\Eloquent\Concerns;

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
        return Attribute::get(
            fn ($_, array $attributes): string => ($attributes[$this->dateAttributeName()] === '0000-00-00' &&
                $attributes[$this->timeAttributeName()] === '00:00:00')
                ? 'Belum Dilayani'
                : 'Sudah Dilayani'
        );
    }
}
