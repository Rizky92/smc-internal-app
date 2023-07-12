<?php

namespace App\Support\Mixins;

use Closure;

class CustomEloquentCollections
{
    public function hasAttributeValue(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Database\Eloquent\Collection
         * 
         * @param  string $name
         * @param  mixed $value
         * 
         * @return bool
         */
        return function(string $name, $value): bool {
            return $this
                ->filter()
                ->containsStrict($value)
        };
    }
}