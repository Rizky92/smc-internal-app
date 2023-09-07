<?php

namespace App\Models\Kepegawaian;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\Eloquent\Model;
use Illuminate\Support\Str;

class Pegawai extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $table = 'pegawai';

    public $incrementing = false;

    public $timestamps = false;
}
