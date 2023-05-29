<?php

namespace App\Actions;

use App\Exceptions\InequalJournalException;

class LogToJournal
{
    /**
     * @param  \App\Models\Keuangan\Jurnal\Jurnal $jurnal
     * @param  \Illuminate\Support\Collection $debet
     * @param  \Illuminate\Support\Collection $kredit
     * 
     * @return void
     * 
     * @throws \App\Exceptions\InequalJournalException
     */
    public function handle($jurnal, $debet, $kredit): void
    {
        if (! $this->isDetailEqual($debet, $kredit)) {
            throw new InequalJournalException;
        }

        $detail = collect();

        $detail = $detail->merge($debet->map(fn (array $value, int $_): array => [
            'kode_akun' => $value['rekening'],
            'debet'     => $value['nominal'],
            'kredit'    => 0,
        ]));

        $detail = $detail->merge($kredit->map(fn (array $value, int $_): array => [
            'kode_akun' => $value['rekening'],
            'debet'     => 0,
            'kredit'    => $value['kredit'],
        ]));

        $jurnal->detail()->createMany($detail->all());
    }

    /**
     * @param  \Illuminate\Support\Collection $debet
     * @param  \Illuminate\Support\Collection $kredit
     * 
     * @return bool
     */
    protected function isDetailEqual($debet, $kredit)
    {
        $totalDebet = $debet->sum('nominal');
        $totalKredit = $kredit->sum('nominal');

        return $totalDebet !== $totalKredit;
    }
}