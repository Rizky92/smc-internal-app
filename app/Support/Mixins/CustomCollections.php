<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
         */
        return fn (string $glue, string $finalGlue = ''): Stringable =>
            new Stringable($this->join($glue, $finalGlue));
    }

    /**
     * @return \Closure(): bool
     */
    public function isAssoc(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Collection
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
         */
        return fn (bool $condition, ...$values): Collection =>
            $this->when($condition, fn (Collection $c) => 
                $c->push($values)
            );
    }

    /**
     * @return \Closure(bool, ...mixed): \Illuminate\Support\Collection
     */
    public function pushUnless(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Collection
         */
        return fn (bool $condition, ...$values): Collection => 
            $this->unless($condition, fn (Collection $c) =>
                $c->push($values)
            );
    }

    /**
     * @return \Closure(string|null, int): \Illuminate\Support\Collection
     */
    public function whereLike(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Collection
         */
        return fn (?string $search = null, int $maxDistance = 1): Collection =>
            $this->filter(fn (string $v): bool =>
                empty($search) ?: levenshtein($v, $search) <= $maxDistance
            );
    }

    /**
     * @return \Closure(string|null, int): bool
     */
    public function containsLike(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Collection
         */
        return fn (?string $search = null, int $maxDistance = 1): bool =>
            $this->contains(fn (string $v): bool =>
                empty($search) ?: levenshtein($v, $search) <= $maxDistance
            );
    }

    /**
     * @return \Closure(string[]|string): bool
     */
    public function doesntHave(): Closure
    {
        /**
         * @psalm-scope-this Illuminate\Support\Collection 
         * 
         * @param  ...string $key
         */
        return function ($key): bool {
            /** @var \Illuminate\Support\Collection $this */

            $key = is_string($key) ? $key : func_get_args();

            if ($this->isAssoc() && is_string($key)) {
                return ! $this->has($key);
            }

            if (is_string($key) && Str::startsWith($key, '*.')) {
                $key = Str::remove('*.', $key);
            } elseif (is_array($key)) {
                $map = fn (string $value): string => (is_string($value) && Str::startsWith($value, '*.'))
                    ? Str::remove('*.', $value)
                    : $value;

                $key = array_map($map, $key);
            }

            $check = false;

            $this->each(function (array $value) use ($key, &$check) {
                $check = Arr::has($value, $key);

                if (!$check) {
                    return $check;
                }
            });

            return !$check;
        };
    }
}
