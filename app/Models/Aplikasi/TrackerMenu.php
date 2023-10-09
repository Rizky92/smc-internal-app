<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TrackerMenu extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'trackermenu';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLihatAktivitasUser(Builder $query, string $userId): Builder
    {
        return $query
            ->where('user_id', $userId)
            ->orderByDesc('waktu');
    }
}
