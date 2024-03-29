<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Penjamin extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kd_pj',
        'png_jawab',
        'nama_perusahaan',
    ];

    public function namaPenjamin(): Attribute
    {
        return Attribute::get(
            fn (): string => ! in_array($this->nama_perusahaan, ['-', ''])
                ? $this->nama_perusahaan
                : $this->png_jawab
        );
    }
}
