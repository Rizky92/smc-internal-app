<?php

namespace App\Models\Kepegawaian;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pegawai extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $table = 'pegawai';

    public $incrementing = false;

    public $timestamps = false;
}
