<?php

namespace App\Support;

class MixinEloquentBuilder
{
    /**
     * @return \Closure(string): bool
     */
    public function isEagerLoaded()
    {
        /** @psalm-scope-this Illuminate\Database\Eloquent\Builder */
        return fn (string $name): bool => isset($this->eagerLoad[$name]);
    }
}