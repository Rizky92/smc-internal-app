<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class MixinCollections
{
    /**
     * @return Closure(array-key, mixed): Collection
     */
    public function set(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return function ($key, $value) {
            $this->items[$key] = $value;

            return $this;
        };
    }

    /**
     * @return Closure(bool, mixed): Collection
     */
    public function mergeWhen(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (bool $expression, $values): Collection => $this->when($expression, fn (Collection $c): Collection => $c->merge($values));
    }

    /**
     * @return Closure(): Collection
     */
    public function dot(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (): Collection => new Collection(Arr::dot($this->items));
    }

    /**
     * @return Closure(int|string|null): mixed
     */
    public function getByDot(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn ($key) => Arr::get($this->items, $key);
    }

    /**
     * @return Closure(string, string): Stringable
     */
    public function joinStr(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (string $glue, string $finalGlue = ''): Stringable => new Stringable($this->join($glue, $finalGlue));
    }

    /**
     * @return Closure(): bool
     */
    public function isAssoc(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return function (): bool {
            $keys = array_keys($this->items);

            return array_keys($keys) !== $keys;
        };
    }

    /**
     * @return Closure(): bool
     */
    public function isList(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return function (): bool {
            $keys = array_keys($this->items);

            return array_keys($keys) === $keys;
        };
    }

    /**
     * @return Closure(bool $condition, mixed ...$values): Collection
     */
    public function pushIf(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (bool $condition, ...$values): Collection => $this->when($condition, fn (Collection $c) => $c->push($values));
    }

    /**
     * @return Closure(bool, ...mixed): \Illuminate\Support\Collection
     */
    public function pushUnless(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (bool $condition, ...$values): Collection => $this->unless($condition, fn (Collection $c) => $c->push($values));
    }

    /**
     * @return Closure(string|null, int): Collection
     */
    public function whereLike(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (?string $search = null, int $maxDistance = 1): Collection => $this->filter(
            fn (string $v): bool => empty($search) ?: levenshtein($v, $search) <= $maxDistance
        );
    }

    /**
     * @return Closure(string|null, int): bool
     */
    public function containsLike(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return fn (?string $search = null, int $maxDistance = 1): bool => $this->contains(
            fn (string $v): bool => empty($search) ?: levenshtein($v, $search) <= $maxDistance
        );
    }

    /**
     * @return Closure(string|mixed): bool
     */
    public function doesntHave(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Collection */
        return function (...$key): bool {
            /** @var Collection $this */
            $key = count($key) > 1 ? $key[array_keys($key)[0]] : $key;

            if ($this->isAssoc() && is_string($key)) {
                return ! $this->has($key);
            }

            if (is_string($key) && Str::startsWith($key, '*.')) {
                $key = Str::remove('*.', $key);
            } elseif (is_array($key)) {
                $map = fn (string $value): string => (Str::startsWith($value, '*.'))
                    ? Str::remove('*.', $value)
                    : $value;

                $key = array_map($map, $key);
            }

            $check = false;

            $this->each(function (array $value) use ($key, &$check) {
                $check = Arr::has($value, $key);

                if (! $check) {
                    return $check;
                }
            });

            return ! $check;
        };
    }
}
