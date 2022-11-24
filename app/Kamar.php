<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $primaryKey = 'kd_kamar';

    protected $keyType = 'string';

    protected $table = 'kamar';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bangsal()
    {
        return $this->belongsTo('App\Bangsal', 'kd_bangsal');
    }
}
