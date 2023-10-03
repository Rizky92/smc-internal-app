<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use App\Database\Eloquent\Model;

class Golongan extends Model
{
    use Sortable, Searchable;

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
    protected $table = 'golongan_barang';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'kode';

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
    protected $searchColumns = ['kode', 'nama'];
}
