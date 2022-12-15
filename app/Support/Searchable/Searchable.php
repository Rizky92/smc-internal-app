<?php

namespace App\Support\Searchable;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

trait Searchable
{
    public function scopeSearch(Builder $query, string $search): Builder
    {
        if (! is_a($this, SearchableContract::class)) {
            $className = class_basename(SearchableContract::class);
            throw new InvalidArgumentException("Unable to perform search: {$className} is not implemented properly.");
        }

        if (! method_exists($this, 'searchInColumns')) {
            throw new InvalidArgumentException("Unable to perform search: no columns were used to perform search query.");
        }

        $columns = collect($this->searchInColumns())->shift();
        $query = $query->where($this->searchInColumns[0], 'LIKE', "%{$search}%");

        foreach ($columns as $column) {
            $query = $query->orWhere($column, 'LIKE', "%{$search}%");
        }

        return $query;
    }
}