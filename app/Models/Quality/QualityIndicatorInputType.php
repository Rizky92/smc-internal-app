<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;

class QualityIndicatorInputType extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'QualityIndicatorInputType';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
