<?php

namespace App\Models\RekamMedis;

use App\Casts\AESFromDatabaseCast;
use App\Support\Eloquent\Concerns\MergeCasts;
use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasienUser extends Model
{
    use Sortable, Searchable, MergeCasts;

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
    protected $table = 'personal_pasien';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'no_rkm_medis';

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

    protected function casts(): array
    {
        return [
            'password' => AESFromDatabaseCast::class . ':' . config('khanza.app.passkey'),
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }
}
