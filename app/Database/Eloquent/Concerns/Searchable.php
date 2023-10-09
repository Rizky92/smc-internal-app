<?php

namespace App\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;

trait Searchable
{
    /**
     * @param  \Illuminate\Support\Collection<int, string>|string[]
     * 
     * @return $this
     */
    public function addSearchConditions($columns)
    {
        if ($columns instanceof Collection) {
            $columns = $columns->all();
        }

        $this->searchColumns = array_merge($this->searchColumns, $columns);

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

        if (is_array($columns)) {
            $columns = collect($columns);
        }

        if (property_exists($this, 'searchColumns') && is_array($this->searchColumns)) {
            $searchColumns = collect($this->searchColumns)
                ->map(function (string $column) {
                    if (Str::contains($column, ['(', ')', '<', '>', '=', '.', '-'])) {
                        return $column;
                    }

                    return $this->qualifyColumn($column);
                })
                ->all();

            $columns = $columns->merge($this->qualifyColumns($searchColumns));
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = $columns->merge($this->searchColumns());
        }

        if ($columns->isEmpty()) {
            throw new LogicException("No columns are defined to perform search.");
        }

        // Convert to lowercase, split search queries to each words, filter any white-space character, and wrap each words with "%".
        $search = Str::of($search)
            ->lower()
            ->split('/\s+/')
            ->filter()
            ->map(fn (string $word): string => str($word)->trim()->wrap('%')->value);

        $concatenatedColumns = $columns->joinStr(', ')->wrap("concat_ws(' ', ", ')')->value();

        return $query->when(
            $search->isNotEmpty(),
            fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search, $concatenatedColumns): Builder {
                foreach ($search as $word) {
                    $query->whereRaw("{$concatenatedColumns} like ?", $word);
                }

                return $query;
            })
        );
    }
}
