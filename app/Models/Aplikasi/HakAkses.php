<?php

namespace App\Models\Aplikasi;

use App\Casts\BooleanCast;
use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HakAkses extends Model
{
    use Sortable, Searchable;

    /**
     * The connection name for the model.
     *
     * @var ?string
     */
    protected $connection = 'mysql_smc';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'khanza_mapping_akses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'nama_field';

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
        'nama_field',
        'judul_menu',
        'default_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'default_value' => BooleanCast::class,
    ];

    protected function searchColumns(): array
    {
        return [
            'nama_field',
            DB::raw('ifnull(judul_menu, "")'),
        ];
    }
}
