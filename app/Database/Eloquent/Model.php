<?php

namespace App\Database\Eloquent;

use App\Database\Eloquent\Concerns\MergeCasts;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query\Expression;

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
     * List of columns that uses explicitly defined queries.
     *
     * @var array<string, Expression|string>
     */
    protected $rawColumns = [];
}
