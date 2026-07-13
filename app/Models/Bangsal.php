<?php

namespace App\Models;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\Kamar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bangsal extends Model
{
    /**
     * Daftar gudang farmasi yang dimonitor. Tambahkan/kurangi di sini
     * jika suatu saat cakupan lokasi berubah.
     */
    public const GUDANG_FARMASI = [
        'GF'  => 'GUDANG FARMASI',
        'IFA' => 'FARMASI A',
        'AP'  => 'APOTEK/INSTALASI FARMASI',
        'IFC' => 'INSTALASI FARMASI CATHLAB',
        'IFO' => 'INSTALASI FARMASI OK',
        'IFI' => 'INSTALASI FARMASI RAWAT INAP',
        'IFG' => 'INSTALASI FARMASI IGD',
        'AMB' => 'GUDANG AMBULANCE',
    ];

    public static function gudangFarmasiKeys(): array
    {
        return array_keys(self::GUDANG_FARMASI);
    }

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_bangsal';

    protected $keyType = 'string';

    protected $table = 'bangsal';

    public $incrementing = false;

    public $timestamps = false;

    public function mappingBidang(): BelongsToMany
    {
        return $this->belongsToMany(Bidang::class, 'mapping_bidang', 'bidang_id', 'kd_bangsal', 'id', 'kd_bangsal');
    }

    /**
     * @psalm-return HasMany<Kamar>
     */
    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function scopeInformasiKamar(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            bangsal.nm_bangsal,
            kamar.kelas,
            sum(kamar.status = 'ISI') as total_terisi,
            sum(kamar.status = 'KOSONG') as total_tersedia
        SQL;

        $this->addSearchConditions([
            'bangsal.nm_bangsal',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('kamar', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
            ->where('bangsal.status', '1')
            ->where('kamar.statusdata', '1')
            ->groupBy('bangsal.kd_bangsal', 'kamar.kelas')
            ->orderBy('bangsal.nm_bangsal')
            ->orderBy('kamar.kelas');
    }
}
