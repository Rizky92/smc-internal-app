<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;

class QualityIndicatorInputType extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_input_types';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = ['name'];
}
