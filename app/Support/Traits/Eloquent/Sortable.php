<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public function scopeSortWithColumns(Builder $query, array $columns = [], array $rawColumns = []): Builder
    {
        $query->reorder();

        $mappedColumns = collect($columns)->flatMap(fn ($value, $key) => [$key => $key]);

        if (! empty($rawColumns)) {
            $mappedColumns = $mappedColumns->merge($rawColumns);
        }

        $mappedColumns = $mappedColumns->toArray();
        
        foreach ($columns as $column => $direction) {
            $query->orderBy($mappedColumns[$column], $direction);
        }

        return $query;
    }
}