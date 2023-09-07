<?php

namespace App\Models;

use App\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bangsal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_bangsal';

    protected $keyType = 'string';

    protected $table = 'bangsal';

    public $incrementing = false;

    public $timestamps = false;

    public function mappingBidang(): BelongsToMany
    {
        return $this->belongsToMany(Bidang::class, 'mapping_bidang', 'bidang_id', 'kd_bangsal', 'id', 'kd_bangsal');
    }
}
