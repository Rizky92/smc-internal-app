<?php

namespace App\Database\Eloquent;

use App\Database\Eloquent\Concerns\MergeCasts;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use Searchable, Sortable, MergeCasts;

    /**
     * The columns used for search query
     * 
     * @var string[]
     */
    protected $searchColumns = [];
}
