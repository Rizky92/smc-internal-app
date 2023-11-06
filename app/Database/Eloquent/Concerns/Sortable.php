<?php

namespace App\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TKey
 * @template TValue of \Illuminate\Database\Query\Expression|string
 */
trait Sortable
{
    protected function sortColumns(): array
    {
        return [];
    }

    /**
     * @param  \Illuminate\Support\Collection<TKey, TValue>|array<TValue> $columns
     * 
     * @return $this
     */
    public function addSortColumns($columns)
    {
        $this->sortColumns = collect($this->sortColumns)
            ->merge($columns)
            ->all();

        return $this;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array<string, string> $columns
     * @param  array<string, \Illuminate\Database\Query\Expression<string>|string> $rawColumns
     * @param  array<string, \Illuminate\Database\Query\Expression<string>|string> $initialColumnOrder
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortWithColumns(Builder $query, array $sortColumns = [], array $rawColumns = [], array $initialColumnOrders = []): Builder
    {
        $columns = collect()
            ->merge($this->sortColumns)
            ->merge($this->sortColumns());

        if (empty($sortColumns) && in_array(head($rawColumns), ['asc', 'desc'])) {
            $initialColumnOrders = $rawColumns;
        } else {
            $columns = $columns->merge($rawColumns);
        }

        if (empty($columns)) {
            return $query;
        }

        if (! empty($initialColumnOrders)) {
            $query->reorder();

            foreach ($initialColumnOrders as $column => $order) {
                $query->orderBy($columns->get($column) ?? $column, $order);
            }

            return $query;
        }

        $query->reorder();

        foreach ($sortColumns as $column => $order) {
            $query->orderBy($columns->get($column) ?? $column, $order);
        }

        return $query;
    }
}
