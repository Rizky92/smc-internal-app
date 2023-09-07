<?php

namespace App\Support\Eloquent\Concerns;

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
        if (!empty($initialColumnOrder) && empty($columns)) {
            $rawColumns = collect($rawColumns);

            foreach ($initialColumnOrder as $column => $direction) {
                $query->orderBy($rawColumns->get($column) ?? $column, $direction);
            }

            return $query;
        }

        $query->reorder();

        $mappedColumns = collect($columns)->flatMap(fn ($_, $key): array => [$key => $key]);

        if (!empty($rawColumns)) {
            $mappedColumns = $mappedColumns->merge($rawColumns);
        }

        foreach ($columns as $column => $direction) {
            $query->orderBy($mappedColumns->get($column), $direction);
        }

        return $query;
    }
}
