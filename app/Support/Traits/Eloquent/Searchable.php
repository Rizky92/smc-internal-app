<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;

trait Searchable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param  string $search
     * @param  \Illuminate\Support\Collection<int, string>|array<int, string> $columns
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (is_array($columns)) {
            $columns = collect($columns);
        }

        if (property_exists($this, 'searchColumns') && is_array($this->searchColumns)) {
            $columns = $columns->merge($this->searchColumns);
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = $columns->merge($this->searchColumns());
        }

        if ($columns->isEmpty()) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $search = Str::lower($search);

        $concatenatedColumns = 'lower(convert(concat(' . $columns->join(", ' ', ") . ') using latin1))';

        return $query->when(
            !empty($search),
            fn ($query) => $query->where(fn ($query) => $query->whereRaw("{$concatenatedColumns} like ?", ["%{$search}%"]))
        );
    }
}
