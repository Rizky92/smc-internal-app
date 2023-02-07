<?php

namespace App\Models\Keuangan\Jurnal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurnalNonMedis extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'jurnal_non_medis';

    public $timestamps = false;

    protected $fillable = [
        'no_jurnal',
        'waktu_jurnal',
        'no_faktur',
        'status',
        'ket',
        'nik',
    ];

    public static function refreshModel()
    {
        $latest = static::latest('waktu_jurnal')->first();

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->when(
                !is_null($latest),
                fn ($query) => $query->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->waktu_jurnal),
                fn ($query) => $query->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('keterangan', 'like', '%BAYAR PELUNASAN %% BARANG NON MEDIS NO.FAKTUR %%, OLEH %')
            ->where('keterangan', 'not like', '%adjustmen%')
            ->orderBy('no_jurnal')
            ->chunk(100, function ($jurnal) {
                /** @var \Illuminate\Support\Collection $jurnal */

                $data = $jurnal->map(function ($value, $key) {
                    $ket = Str::of($value->keterangan);

                    $status = $ket->startsWith('BATAL');
                    $noFaktur = (string) $ket->after('NO.FAKTUR ')->beforeLast(',')->trim();
                    $petugas = (string) $ket->after('OLEH ')->trim();

                    return [
                        'no_jurnal' => $value->no_jurnal,
                        'waktu_jurnal' => "{$value->tgl_jurnal} {$value->jam_jurnal}",
                        'no_faktur' => $noFaktur,
                        'status' => $status ? 'Batal' : 'Sudah',
                        'ket' => $value->keterangan,
                        'nik' => $petugas,
                    ];
                });

                static::insert($data->all());
            });
    }
}
