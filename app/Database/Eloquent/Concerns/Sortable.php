<?php

namespace App\Database\Eloquent\Concerns;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

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

    public function addRawColumns($columns, $expression = null): Model
    {
        if (is_string($columns) && $expression) {
            $this->rawColumns = array_merge($this->rawColumns, [$columns => $expression]);

            return $this;
        }

        $this->rawColumns = collect($this->rawColumns)
            ->merge($columns)
            ->all();

        return $this;
    }

    /**
     * @param  Collection<TKey, TValue>|array<TValue>|string  $columns
     * @param  Expression|string|null  $condition
     * @return $this
     */
    public function addSortColumns($columns, $condition = null)
    {
        if (is_string($columns) && $condition) {
            $this->sortColumns = array_merge($this->sortColumns, [$columns => $condition]);

            return $this;
        }

        $this->sortColumns = collect($this->sortColumns)
            ->merge($columns)
            ->all();

        return $this;
    }

    /**
     * @param  array<string, string>  $sortColumns
     * @param  array<string, Expression<string>|string>  $rawColumns
     * @param  array<string, Expression<string>|string>  $initialColumnOrders
     */
    public function scopeSortWithColumns(Builder $query, array $sortColumns = [], array $rawColumns = [], array $initialColumnOrders = []): Builder
    {
        $columns = collect()
            ->merge($this->sortColumns())
            ->when(property_exists($this, 'sortColumns'), fn (Collection $c) => $c->merge($this->sortColumns));

        if (empty($sortColumns) && (empty($initialColumnOrders) || empty($rawColumns))) {
            return $query;
        }

        $rawColumns = collect($rawColumns)
            ->when(property_exists($this, 'rawColumns'), fn (Collection $c) => $c->merge($this->rawColumns))
            ->when(method_exists($this, 'rawColumns'), fn (Collection $c) => $c->merge($this->rawColumns()));

        if (empty($sortColumns) && in_array(head($rawColumns), ['asc', 'desc'])) {
            $initialColumnOrders = $rawColumns;
        } else {
            $rawColumns = collect($rawColumns)
                ->when(property_exists($this, 'rawColumns'), fn (Collection $c) => $c->merge($this->rawColumns))
                ->when(method_exists($this, 'rawColumns'), fn (Collection $c) => $c->merge($this->rawColumns()));

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
