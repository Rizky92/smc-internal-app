<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class BayarPiutang extends Model
{
    use Searchable, Sortable;

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'bayar_piutang';

    public $incrementing = false;

    public $timestamps = false;
}
