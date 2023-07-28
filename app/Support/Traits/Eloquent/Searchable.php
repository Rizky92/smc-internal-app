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
     * @param  \Illuminate\Support\Collection<int, string>|array<array-key, string> $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (is_array($columns)) {
            $columns = collect($columns);
        }

        if (property_exists($this, 'searchColumns') && is_array($this->searchColumns)) {
            $columns = $columns->merge($this->qualifyColumns($this->searchColumns));
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = $columns->merge($this->qualifyColumns($this->searchColumns()));
        }

        if ($columns->isEmpty()) {
            throw new LogicException("No columns are defined to perform search.");
        }

        // Split search queries to each words, convert to lowercase, filter any white-space character, and wrap each words with "%".
        $search = Str::of($search)
            ->lower()
            ->split('/\s+/')
            ->filter()
            ->map(fn (string $word): string => Str::wrap($word, '%'));

        $concatenatedColumns = $columns->joinStr(", ' ', ")->wrap('concat(', ')')->value();

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
