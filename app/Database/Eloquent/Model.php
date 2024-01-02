<?php

namespace App\Database\Eloquent;

use App\Database\Eloquent\Concerns\MergeCasts;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use MergeCasts;
    use Searchable;
    use Sortable;

    /**
     * The columns used for search query.
     *
     * @var string[]
     */
    protected $searchColumns = [];

    /**
     * The initial columns used for column ordering.
     *
     * @var string[]
     */
    protected $sortColumns = [];

    /**
     * List of columns that uses raw queries.
     *
     * @var array<TKey, \Illuminate\Database\Query\Expression|string>
     */
    protected $rawColumns = [];
}
