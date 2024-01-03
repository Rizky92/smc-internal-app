<?php

namespace App\Support;

use Closure;

class MixinStr
{
    /**
     * Determine if a given string does not contain a given substring.
     *
     * @return Closure(string, string|string[]): bool
     */
    public function doesntContain(): Closure
    {
        /** @psalm-scope-this Illuminate\Support\Str */
        return fn ($haystack, $needles): bool => ! static::contains($haystack, $needles);
    }

    /**
     * replace all strings with provided value.
     *
     * @return Closure(string|string[], string, string): string
     */
    public function replaceWith(): Closure
    {
        return function ($replace, string $with, string $value): string {
            if (is_string($replace)) {
                $replace = [$replace];
            }

            foreach ($replace as $char) {
                $value = str_replace($char, $with, $value);
            }

            return $value;
        };
    }

    /**
     * @return Closure(string, string, ?string): string
     */
    public function wrap(): Closure
    {
        return fn (string $value, string $startsWith, ?string $endsWith = null): string => is_null($endsWith)
                ? $startsWith.$value.$startsWith
                : $startsWith.$value.$endsWith;
    }

    /**
     * @return Closure(string): int
     */
    public function parseInt(): Closure
    {
        return fn (string $value): int => intval($value);
    }

    /**
     * @return Closure(string): float
     */
    public function parseDouble(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    /**
     * @return Closure(string): float
     */
    public function parseFloat(): Closure
    {
        return fn (string $value): float => floatval($value);
    }

    /**
     * @return Closure(string): bool
     */
    public function parseBoolean(): Closure
    {
        return fn (string $value): bool => boolval($value);
    }
}
