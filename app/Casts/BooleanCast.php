<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class BooleanCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @return bool
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (! is_string($value)) {
            return false;
        }

        return $value === 'true';
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  bool|mixed  $value
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value ? 'true' : 'false';
    }
}
