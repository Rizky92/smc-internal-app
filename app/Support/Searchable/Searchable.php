<?php

namespace App\Support\Searchable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LogicException;

trait Searchable
{
    /** @var array<int,string> $searchColumns */
    protected $searchColumns;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $search
     * @param  array<int,string> $columns
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (is_array($this->searchColumns)) {
            $columns = array_merge($columns, $this->searchColumns);
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = array_merge($columns, $this->searchColumns());
        }

        if (empty($columns)) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $search = Str::lower($search);

        $query->where(array_shift($columns), 'LIKE', "%{$search}%");

        foreach ($columns as $column) {
            $query->orWhere($column, 'LIKE', "%{$search}%");
        }

        return $query;
    }
}