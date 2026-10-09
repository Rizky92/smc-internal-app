<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Departemen;
use App\Models\Kepegawaian\Pegawai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QualityIndicator extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicators';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'quality_indicator_profile_id',
        'dep_id',
        'person_in_charge',
        'pic_nik',
        'data_source',
        'status',
    ];

    protected function searchColumns(): array
    {
        return [
            DB::raw('(select quality_indicator_profiles.title from quality_indicator_profiles where quality_indicator_profiles.id = quality_indicators.quality_indicator_profile_id)'),
        ];
    }

    /**
     * @param  string|string[]  $depId
     */
    public function scopeDepartemen(Builder $query, $depId): Builder
    {
        return $query->whereIn('dep_id', Arr::wrap($depId));
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorProfile::class, 'quality_indicator_profile_id');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'dep_id', 'dep_id');
    }

    /**
     * Pegawai PIC indikator (mysql_sik). `person_in_charge` tetap menyimpan label jabatan.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pic_nik', 'nik');
    }

    /**
     * Reviewer yang memenuhi syarat untuk indikator ini: pemegang izin review selain PIC-nya (larangan
     * self-review). Fase 2 menambahkan pengecualian penulis analisis.
     *
     * @param  Collection<int, string>  $nikPemegangIzin  hasil User::nikPemegangIzin('mutu.review-analisis.approve')
     * @return Collection<int, string>
     */
    public function reviewerMemenuhiSyarat(Collection $nikPemegangIzin): Collection
    {
        return $nikPemegangIzin->reject(fn (string $nik): bool => $nik === $this->pic_nik)->values();
    }

    public function records(): HasMany
    {
        return $this->hasMany(QualityIndicatorRecord::class, 'indicator_id');
    }
}
