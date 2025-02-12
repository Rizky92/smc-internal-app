<?php

namespace App\EloquentSerialize\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string serialize(\Illuminate\Database\Eloquent\Builder $builder)
 * @method static \Illuminate\Database\Eloquent\Builder unserialize(mixed $package)
 */
class EloquentSerializeFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\EloquentSerialize\Service::class;
    }
}
