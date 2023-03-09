<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Penjamin extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;

    public function namaPenjamin(): Attribute
    {
        return Attribute::get(function () {
            if (!in_array($this->nama_perusahaan, ['-', ''])) {
                return $this->nama_perusahaan;
            }

            return $this->png_jawab;
        });
    }
}
