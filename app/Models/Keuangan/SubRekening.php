<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class SubRekening extends Model
{
    use HasRecursiveRelationships;

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'subrekening';

    public $incrementing = false;

    public $timestamps = false;

    public function getLocalKeyName()
    {
        return 'kd_rek2';
    }

    public function getParentKeyName()
    {
        return 'kd_rek';
    }
}
