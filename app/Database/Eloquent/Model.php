<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * Determine if model should prevent handling actions if primary key is not explicitly set.
     */
    public bool $preventsActionWithoutPrimaryKey = false;

    /** @var callable */
    protected static $actionWithoutPrimaryKeyCallback;

    public static function preventActionWithoutPrimaryKey(bool $value = true): void
    {
        static::$preventsActionWithoutPrimaryKey = $value;
    }

    /**
     * @param  \Closure|callable $callback
     */
    public static function handleActionsWithoutPrimaryKeyUsing($callback): void
    {
        static::$actionWithoutPrimaryKeyCallback = $callback;
    }
}