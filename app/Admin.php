<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $primaryKey = 'usere';

    protected $keyType = 'string';

    protected $table = 'admin';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'nama',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            return $query->selectRaw('"Admin Utama" nama');
        });
    }
}
