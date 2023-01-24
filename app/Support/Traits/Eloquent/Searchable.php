<?php

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
        if (property_exists($this, 'searchColumns') && is_array($this->searchColumns)) {
            $columns = array_merge($columns, $this->searchColumns);
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = array_merge($columns, $this->searchColumns());
        }

        if (empty($columns)) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $search = Str::lower($search);

        $concatenatedColumns = 'concat(' . collect($columns)->join(", ' ', ") . ')';

        return $query->when(
            !empty($search),
            fn (Builder $query) => $query->whereRaw("{$concatenatedColumns} like ?", ["%{$search}%"])
        );
    }
}
