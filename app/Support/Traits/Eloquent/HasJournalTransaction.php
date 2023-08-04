<?php

namespace App\Support\Traits\Eloquent;

use App\Models\Keuangan\Jurnal\Jurnal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasJournalTransaction
{
    abstract public static function keterangan(): string;

    abstract protected function getJournalForeignKey(): string;

    public function jurnal(): HasMany
    {
        return $this->hasMany(Jurnal::class, $this->getJournalForeignKey(), 'no_bukti');
    }

    public function scopeEntriJurnal(Builder $query, $noBukti = null): Builder
    {
        return $query
            ->with('jurnal.detail')
            ->whereHas('jurnal', fn (Builder $q): Builder => $q
                ->when(is_array($noBukti), fn (Builder $q): Builder => $q->whereIn('no_bukti', $noBukti))
                ->when(is_string($noBukti), fn (Builder $q): Builder => $q->where('no_bukti', $noBukti))
            );
    }
}