<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class Export extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'exports';

    protected $primaryKey = 'id';

    /*
     * Tabel ini tidak punya kolom created_at maupun updated_at. Isinya data
     * antara yang umurnya hanya selama satu proses export, jadi waktu tulisnya
     * tidak pernah dipakai.
     */
    public $timestamps = false;

    /*
     * `id` bukan lagi AUTO_INCREMENT, melainkan nomor urut per sesi export yang
     * dihasilkan row_number() di dalam INSERT ... SELECT. Primary key di level
     * database berupa gabungan (export_session_id, id), tapi hal itu tidak
     * perlu diketahui Eloquent: semua query pada tabel ini sudah disaring per
     * sesi, sehingga whereKey() atas `id` saja tetap menunjuk baris yang benar.
     */
    public $incrementing = false;

    protected $keyType = 'int';
}
