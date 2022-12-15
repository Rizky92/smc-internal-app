<?php

namespace App\Support\Searchable;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

trait Searchable
{
    /**
     * Array of columns to search within.
     * 
     * @var array $searchColumns
     */
    protected $searchColumns = [];

    /**
     * Local scope of performing search in columns within model.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $search
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBeginSearch(Builder $query, string $search): Builder
    {
        $columns = $this->searchColumns;

        if (method_exists($this, 'searchColumns')) {
            $columns = array_merge($this->searchColumns());
        }

        if (empty($columns)) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $query = $query->where($columns[0], 'LIKE', "%{$search}%");

        array_shift($columns);

        foreach ($columns as $column) {
            $query = $query->orWhere($column, 'LIKE', "%{$search}%");
        }

        return $query;
    }
}