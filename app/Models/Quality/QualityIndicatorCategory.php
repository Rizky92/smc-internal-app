<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;

class QualityIndicatorCategory extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'QualityIndicatorCategory';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
