<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Pegawai extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $table = 'pegawai';

    public $incrementing = true;

    public $timestamps = false;

    public static function findNIP(string $nip): self
    {
        $pegawai = static::query()
            ->where('nik', $nip)
            ->first();

        if (! $pegawai) {
            throw new ModelNotFoundException;
        }

        return $pegawai;
    }
}
