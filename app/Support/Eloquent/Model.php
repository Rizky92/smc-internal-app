<?php

namespace App\Support\Eloquent;

use App\Support\Eloquent\Concerns\MergeCasts;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use MergeCasts;
}
