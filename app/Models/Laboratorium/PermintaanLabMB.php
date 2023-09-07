<?php

namespace App\Models\Laboratorium;

use App\Support\Eloquent\Concerns\StatusOrder;
use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Reedware\LaravelCompositeRelations\CompositeHasMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class PermintaanLabMB extends Model
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
    protected $table = 'permintaan_labmb';

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

    protected $appends = ['status_order'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tgl_permintaan' => 'date',
        'jam_permintaan' => 'datetime',
        'tgl_sampel' => 'date',
        'jam_sampel' => 'datetime',
        'tgl_hasil' => 'date',
        'jam_hasil' => 'datetime',
    ];

    /** 
     * @var string[]
     */
    protected $searchColumns = [
        //
    ];

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
            ->where('status', 'MB');
    }
}
