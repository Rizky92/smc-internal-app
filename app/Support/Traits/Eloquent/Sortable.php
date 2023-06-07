<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array<string, string> $columns
     * @param  array<string, \Illuminate\Database\Query\Expression<string>|string> $rawColumns
     * @param  array<string, \Illuminate\Database\Query\Expression<string>|string> $initialColumnOrder
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortWithColumns(Builder $query, array $columns = [], array $rawColumns = [], array $initialColumnOrder = []): Builder
    {
        $mappedColumns = collect($columns)->flatMap(fn ($_, $key): array => [$key => $key]);

        if (!empty($rawColumns)) {
            $mappedColumns = $mappedColumns->merge($rawColumns);
        }

        if (!empty($initialColumnOrder) && empty($columns)) {
            foreach ($initialColumnOrder as $column => $direction) {
                $query->orderBy($mappedColumns->get($column), $direction);
            }

            return $query;
        }

        $query->reorder();

        foreach ($columns as $column => $direction) {
            $query->orderBy($mappedColumns->get($column), $direction);
        }

        return $query;
    }
}
