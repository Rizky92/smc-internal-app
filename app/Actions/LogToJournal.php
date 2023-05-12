<?php

namespace App\Actions;

use App\Exceptions\InequalJournalException;

class LogToJournal
{
    /**
     * @param  \App\Models\Keuangan\Jurnal\Jurnal $jurnal
     * @param  \Illuminate\Support\Collection|array|null $debet
     * @param  \Illuminate\Support\Collection|array|null $kredit
     * 
     * @return void
     * 
     * @throws \App\Exceptions\InequalJournalException
     */
    public function handle($jurnal, $debet, $kredit)
    {
        if (! $this->isDetailEqual($debet, $kredit)) {
            throw new InequalJournalException;
        }

        $detail = collect();

        dd($debet, $kredit, $detail);

        $detail = $detail->merge($debet->map(fn ($value, $key) => [
            'kode_akun' => $value['rekening'],
            'debet' => $value['nominal'],
            'kredit' => 0,
        ]));

        $detail = $detail->merge($kredit->map(fn ($value, $key) => [
            'kode_akun' => $value['rekening'],
            'debet' => 0,
            'kredit' => $value['kredit'],
        ]));

        $jurnal->detail()->createMany($detail->all());
    }

    /**
     * @param  \Illuminate\Support\Collection|array|null $debet
     * @param  \Illuminate\Support\Collection|array|null $kredit
     * 
     * @return bool
     */
    protected function isDetailEqual($debet, $kredit)
    {
        if (is_null($debet) || is_null($kredit)) {
            return false;
        }

        if (is_array($debet)) {
            $debet = collect($debet);
        }

        if (is_array($kredit)) {
            $kredit = collect($kredit);
        }

        $totalDebet = $debet->sum('nominal');
        $totalKredit = $kredit->sum('nominal');

        return $totalDebet !== $totalKredit;
    }
}