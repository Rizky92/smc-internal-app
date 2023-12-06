<?php

namespace App\Database\Eloquent\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Searchable
{
    protected function searchColumns(): array
    {
        return [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>|string[] $columns
     * 
     * @return $this
     */
    public function addSearchConditions($columns)
    {
        $this->searchColumns = collect($this->searchColumns)
            ->merge($columns)
            ->all();

        return $this;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $search
     * @param  \Illuminate\Support\Collection<int, string>|array<array-key, string> $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $searchBindings = [];

        $columns = collect($columns)
            ->merge($this->searchColumns)
            ->merge($this->searchColumns())
            ->map(function ($column) use (&$searchBindings) {
                if (is_array($column)) {
                    if (isset($column['bindings'])) {
                        $searchBindings = array_merge($searchBindings, $column['bindings']);
                    }

                    if (isset($column['query'])) {
                        $column = $column['query'];
                    }
                }

                if ($column instanceof Expression) {
                    return $column->getValue();
                }

                if (Str::doesntContain($column, ['(', ')', '<', '>', '=', '.', '-'])) {
                    return $this->qualifyColumn($column);
                }

                return $column;
            });

        if ($columns->isEmpty()) {
            throw new Exception("No columns are defined to perform search.");
        }

        // Convert to lowercase, split search queries to each words, filter any white-space characters, and wrap each words with "%".
        $search = str($search)
            ->lower()
            ->split('/\s+/')
            ->filter()
            ->map(fn (string $word): string => str($word)->trim()->wrap('%')->value());

        $concatenatedColumns = $columns->joinStr(', ')->wrap("concat_ws(' ', ", ') like ?')->value();

        return $query->when(
            $search->isNotEmpty(),
            fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search, $concatenatedColumns, $searchBindings): Builder {
                foreach ($search as $word) {
                    $query->whereRaw($concatenatedColumns, [...$searchBindings, $word]);
                }

                return $query;
            })
        );
    }
}
