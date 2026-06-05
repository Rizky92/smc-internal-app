<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;

class QualityIndicatorCategory extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_categories';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = ['name'];
}
