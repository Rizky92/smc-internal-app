<?<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LogicException;

trait Searchable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $search
     * @param  array<int, string> $columns
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (method_exists($this, 'searchColumns')) {
            $columns = array_merge($columns, $this->searchColumns());
        }

        if (empty($columns)) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $search = Str::lower($search);

        return $query->where(function (Builder $query) use ($columns, $search) {
            $query->where(array_shift($columns), 'LIKE', "%{$search}%");
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$search}%");
            }

            return $query;
        });
    }
}