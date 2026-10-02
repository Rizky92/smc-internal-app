<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;

class ProofMethod extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_proof_methods';

    protected $fillable = ['kode', 'nama'];
}
