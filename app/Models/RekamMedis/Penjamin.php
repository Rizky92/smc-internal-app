<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Concerns\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Database\Eloquent\Model;

class Penjamin extends Model
{
    use Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;

    protected array $searchColumns = [
        'kd_pj',
        'png_jawab',
        'nama_perusahaan',
    ];

    public function namaPenjamin(): Attribute
    {
        return Attribute::get(
            fn (): string =>
            !in_array($this->nama_perusahaan, ['-', ''])
                ? $this->nama_perusahaan
                : $this->png_jawab
        );
    }
}
