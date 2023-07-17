<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait StatusOrder
{
    protected function timeAttributeName(): array
    {
        return [
            'tgl' => 'tgl_hasil',
            'jam' => 'jam_hasil',
        ];
    }

    public function statusOrder(): Attribute
    {
        return Attribute::get(fn ($_, array $attributes): string => 
            ($attributes[$this->timeAttributeName()['tgl']] === '0000-00-00' &&
             $attributes[$this->timeAttributeName()['jam']] === '00:00:00')
                ? 'Belum Dilayani'
                : 'Sudah Dilayani'
        );
    }
}