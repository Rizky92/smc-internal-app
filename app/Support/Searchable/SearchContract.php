<?php

namespace App\Support\Searchable;

use Illuminate\Database\Eloquent\Builder;

interface SearchContract
{
    public function searchInColumns(): array;

    public function scopeSearch(Builder $query, string $search): Builder;
}