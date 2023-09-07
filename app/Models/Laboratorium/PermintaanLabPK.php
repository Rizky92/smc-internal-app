<?php

namespace App\Models\Laboratorium;

use App\Support\Eloquent\Concerns\StatusOrder;
use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Reedware\LaravelCompositeRelations\CompositeHasMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class PermintaanLabPK extends Model
{
    use Sortable, Searchable, HasCompositeRelations, StatusOrder;

    /**
     * The connection name for the model.
     *
     * @var ?string
     */
    protected $connection = 'mysql_sik';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permintaan_lab';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'noorder';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        // 
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 
    ];

    /** 
     * @var string[]
     */
    protected $searchColumns = [
        //
    ];

    protected $appends = ['status_order'];

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function hasil(): CompositeHasMany
    {
        return $this
            ->compositeHasMany(
                HasilPeriksaLab::class,
                ['no_rawat', 'tgl_periksa', 'jam'],
                ['no_rawat', 'tgl_hasil', 'jam_hasil'],
            )
            ->where('status', 'PK');
    }
}
