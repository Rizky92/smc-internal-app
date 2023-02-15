<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class DetailJurnal extends Model
{
    use Sortable, Searchable;

    protected $primaryKey = 'no_jurnal';

    protected $keyType = 'string';

    protected $table = 'detailjurnal';

    public $incrementing = false;

    public $timestamps = false;

    
}
