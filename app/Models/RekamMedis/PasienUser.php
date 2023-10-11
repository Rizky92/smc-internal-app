<?php

namespace App\Models\RekamMedis;

use App\Casts\AESFromDatabaseCast;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasienUser extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'personal_pasien';

    protected $primaryKey = 'no_rkm_medis';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

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
