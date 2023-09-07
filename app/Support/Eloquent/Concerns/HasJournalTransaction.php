<?php

namespace App\Support\Eloquent\Concerns;

use App\Models\Keuangan\Jurnal\Jurnal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasJournalTransaction
{
    abstract protected function getJournalForeignKey(): string;

    public function jurnal(): HasMany
    {
        return $this->hasMany(Jurnal::class, $this->getJournalForeignKey(), 'no_bukti');
    }

    public function scopeEntriJurnal(Builder $query): Builder
    {
        return $query->with('jurnal.detail');
    }
}
