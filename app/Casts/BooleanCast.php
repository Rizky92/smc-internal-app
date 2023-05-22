<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class BooleanCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string $key
     * @param  string $value
     * @param  array $attributes
     * 
     * @return bool
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $value === 'true';
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string $key
     * @param  bool $value
     * @param  array $attributes
     * 
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value ? 'true' : 'false';
    }
}
