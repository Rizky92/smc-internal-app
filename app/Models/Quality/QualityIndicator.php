<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;

class QualityIndicator extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicators';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}
