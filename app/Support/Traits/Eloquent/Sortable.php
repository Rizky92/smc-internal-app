<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public function scopeSortWithColumns(Builder $query, array $columns = [], array $rawColumns = [], array $initialColumnOrder = []): Builder
    {
        if (!empty($initialColumnOrder) && empty($columns)) {
            foreach ($initialColumnOrder as $column => $direction) {
                $query->orderBy($column, $direction);
            }

            return $query;
        }

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