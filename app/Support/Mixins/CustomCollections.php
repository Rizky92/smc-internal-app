<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class CustomCollections
{
    /**
     * @return \Closure(string, string): \Illuminate\Support\Stringable
     */
    public function joinStr(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return function (string $glue, string $finalGlue = ''): Stringable {
            return new Stringable($this->join($glue, $finalGlue));
        };
    }

    /**
     * @return \Closure(): bool
     */
    public function isAssoc(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return function (): bool {
            $keys = array_keys($this->items);

            return array_keys($keys) !== $keys;
        };
    }

    /**
     * @return \Closure(): bool
     */
    public function isList(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return function (): bool {
            $keys = array_keys($this->items);

            return array_keys($keys) === $keys;
        };
    }

    /**
     * @return \Closure(bool $condition, mixed ...$values): \Illuminate\Support\Collection
     */
    public function pushIf(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return fn (bool $condition, ...$values): Collection =>
            $this->when($condition, fn (Collection $c) => 
                $c->push($values));
    }

    /**
     * @return \Closure(bool, ...mixed): \Illuminate\Support\Collection
     */
    public function pushUnless(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return fn (bool $condition, ...$values): Collection => 
            $this->unless($condition, fn (Collection $c) =>
                $c->push($values));
    }

    /**
     * @return \Closure(string|null, int): \Illuminate\Support\Collection
     */
    public function whereLike(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return function (?string $search = null, int $looseRange = 1): Collection {
            return $this->filter(function (string $v) use ($search, $looseRange): bool {
                if (empty($search)) return true;

                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }

    /**
     * @return \Closure(string|null, int): bool
     */
    public function containsLike(): Closure
    {
        /** 
         * @psalm-scope-this Illuminate\Support\Collection
         * @var \Illuminate\Support\Collection $this
         */
        return function (?string $search = null, int $looseRange = 1): bool {
            return $this->contains(function (string $v) use ($search, $looseRange): bool {
                if (empty($search)) return false;
                
                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }
}
