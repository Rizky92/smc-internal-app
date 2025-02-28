<?php

namespace App\Models\Laboratorium;

use App\Database\Eloquent\Model;
use App\Models\Laboratorium\Concerns\StatusOrder;
use Reedware\LaravelCompositeRelations\CompositeHasMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class PermintaanLabMB extends Model
{
    use HasCompositeRelations;
    use StatusOrder;

    protected $connection = 'mysql_sik';

    protected $table = 'permintaan_labmb';

    protected $primaryKey = 'noorder';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $appends = ['status_order'];

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function hasil(): CompositeHasMany
    {
        return $this
            ->compositeHasMany(
                PeriksaLab::class,
                ['no_rawat', 'tgl_periksa', 'jam'],
                ['no_rawat', 'tgl_hasil', 'jam_hasil'],
            )
            ->where('status', 'MB');
    }
}
