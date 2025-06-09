<?php

namespace App\Models\Radiologi;

use App\Database\Eloquent\Model;
use App\Models\Laboratorium\Concerns\StatusOrder;
use Reedware\LaravelCompositeRelations\CompositeHasMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class PermintaanRadiologi extends Model
{
    use HasCompositeRelations;
    use StatusOrder;

    protected $connection = 'mysql_sik';

    protected $table = 'permintaan_radiologi';

    protected $primaryKey = 'noorder';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function hasil(): CompositeHasMany
    {
        return $this
            ->compositeHasMany(
                PeriksaRadiologi::class,
                ['no_rawat', 'tgl_periksa', 'jam'],
                ['no_rawat', 'tgl_hasil', 'jam_hasil']
            );
    }
}
