<?php

namespace App\Models\Dapur;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BarangDapur extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'dapurbarang';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kode_brng',
        'nama_brng',
        'kode_sat',
        'jenis',
    ];

    public function scopeDenganMinmax(Builder $query): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            dapurbarang.kode_brng,
            dapurbarang.nama_brng,
            ifnull(dapursuplier.kode_suplier, '-') kode_suplier,
            ifnull(dapursuplier.nama_suplier, '-') nama_suplier,
            dapurbarang.jenis,
            kodesatuan.satuan,
            ifnull(minmax.stok_min, 0) stok_min,
            ifnull(minmax.stok_max, 0) stok_max,
            dapurbarang.stok,
            if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0) saran_order,
            dapurbarang.harga,
            dapurbarang.harga * if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0) total_harga
            SQL;

        $this->addSearchConditions([
            'dapurbarang.kode_brng',
            'dapurbarang.nama_brng',
            "ifnull(dapursuplier.kode_suplier, ('-')",
            "ifnull(dapursuplier.nama_suplier, ('-')",
            'kodesatuan.satuan',
        ]);

        $this->addRawColumns([
            'kode_suplier' => DB::raw("ifnull(dapursuplier.kode_suplier, '-')"),
            'nama_suplier' => DB::raw("ifnull(dapursuplier.nama_suplier, '-')"),
            'stokmin'      => DB::raw('ifnull(minmax.stok_min, 0)'),
            'stokmax'      => DB::raw('ifnull(minmax.stok_max, 0)'),
            'saran_order'  => DB::raw('if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0)'),
            'total_harga'  => DB::raw('if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), dapurbarang.harga * ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0)'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('kodesatuan', 'dapurbarang.kode_sat', 'kodesatuan.kode_sat')
            ->leftJoin(sprintf('%s.minmax_stok_dapur as minmax', $db), 'dapurbarang.kode_brng', 'minmax.kode_brng')
            ->leftJoin('dapursuplier', 'minmax.kode_suplier', 'dapursuplier.kode_suplier')
            ->where('dapurbarang.status', '1');
    }

    public function scopeDaruratStok(Builder $query, bool $saranOrderNol = true): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            dapurbarang.kode_brng,
            dapurbarang.nama_brng,
            ifnull(dapursuplier.nama_suplier, '-') nama_suplier,
            dapurbarang.jenis,
            kodesatuan.satuan,
            ifnull(minmax.stok_min, 0) stok_min,
            ifnull(minmax.stok_max, 0) stok_max,
            dapurbarang.stok,
            if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0) saran_order,
            dapurbarang.harga,
            dapurbarang.harga * if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0) total_harga
            SQL;

        $this->addSearchConditions([
            'dapurbarang.kode_brng',
            'dapurbarang.nama_brng',
            "ifnull(dapursuplier.nama_suplier, '-')",
            'dapurbarang.jenis',
            'kodesatuan.satuan',
        ]);

        $this->addRawColumns([
            'nama_supplier' => DB::raw("ifnull(dapursuplier.nama_suplier, '-')"),
            'stokmin'       => DB::raw('ifnull(minmax.stok_min, 0)'),
            'stokmax'       => DB::raw('ifnull(minmax.stok_max, 0)'),
            'saran_order'   => DB::raw('if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0)'),
            'total_harga'   => DB::raw('dapurbarang.harga * if(dapurbarang.stok <= ifnull(minmax.stok_min, 0), ifnull(ifnull(minmax.stok_max, ifnull(minmax.stok_min, 0)) - dapurbarang.stok, 0), 0)'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('kodesatuan', 'dapurbarang.kode_sat', 'kodesatuan.kode_sat')
            ->leftJoin(sprintf('%s.minmax_stok_dapur as minmax', $db), 'dapurbarang.kode_brng', 'minmax.kode_brng')
            ->leftJoin('dapursuplier', 'minmax.kode_suplier', 'dapursuplier.kode_suplier')
            ->where('dapurbarang.status', '1')
            ->whereColumn('dapurbarang.stok', '<=', DB::raw('ifnull(minmax.stok_min, 0)'))
            ->when(! $saranOrderNol, fn (Builder $query) => $query->whereRaw('ifnull(ifnull(minmax.stok_max, 0) - dapurbarang.stok, 0) > 0'));
    }
}
