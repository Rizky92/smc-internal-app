<?php

namespace App\Support\Traits\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LogicException;

trait Searchable
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param  string $search
     * @param  \Illuminate\Support\Collection<int, string>|array<int, string> $columns
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \LogicException
     */
    public function scopeSearch(Builder $query, string $search, $columns = []): Builder
    {
        if (is_array($columns)) {
            $columns = collect($columns);
        }

        if (property_exists($this, 'searchColumns') && is_array($this->searchColumns)) {
            $columns = $columns->merge($this->searchColumns);
        }

        if (method_exists($this, 'searchColumns')) {
            $columns = $columns->merge($this->searchColumns());
        }

        if ($columns->isEmpty()) {
            throw new LogicException("No columns are defined to perform search.");
        }

        $search = Str::of($search)->lower()->split('/\s+/');

        $concatenatedColumns = 'lower(convert(concat(' . $columns->join(", ' ', ") . ') using latin1))';

        return $query->when(
            $search->isNotEmpty(),
            function ($query) use ($search, $concatenatedColumns) {
                $firstWord = $search->pop();

                $query->whereRaw("{$concatenatedColumns} like ?", ["%{$firstWord}%"]);
                
                foreach ($search as $word) {
                    $query->orWhereRaw("{$concatenatedColumns} like ?", ["%{$word}%"]);
                }

                return $query;
            }
        );
    }
}
