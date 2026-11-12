<?php

namespace App\Jobs\Keuangan\Concerns;

use App\Exceptions\ImportTarifException;
use App\Support\QueryErrorTranslator;
use Generator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
 * Shared row handling for the Import Tarif jobs, so every importer reports
 * the same Excel row number and the same messages.
 */
trait ImportsTarifRows
{
    /**
     * Data rows keyed by their Excel row number (header is row 1). Blank rows
     * are kept by the reader so numbering matches Excel, then skipped here.
     *
     * @return Generator<int, array<string, mixed>>
     *
     * @throws ImportTarifException when the file has no data rows
     */
    protected function dataRows(SimpleExcelReader $reader): Generator
    {
        $reader->getReader()->setShouldPreserveEmptyRows(true);

        $found = false;

        foreach ($reader->getRows() as $index => $row) {
            if (collect($row)->every(fn ($value) => $this->blankCell($value))) {
                continue;
            }

            $found = true;

            yield $index + 2 => $row;
        }

        if (! $found) {
            throw new ImportTarifException('Tidak ada data untuk diimport');
        }
    }

    /**
     * @param  mixed  $value
     */
    protected function blankCell($value): bool
    {
        return $value === null
            || (is_string($value) && trim(str_replace(["\u{00A0}", "\u{202F}"], ' ', $value)) === '');
    }

    /**
     * Parse the given rupiah columns of a row.
     *
     * @param  array<string, mixed>  $data  DB column => cell value
     * @param  array<string, string>  $headerMapping  Excel header => DB column
     * @param  list<string>  $columns
     * @return array<string, mixed>
     *
     * @throws ImportTarifException
     */
    protected function parseAmounts(int $line, array $data, array $headerMapping, array $columns): array
    {
        $headers = array_flip($headerMapping);

        foreach ($columns as $column) {
            try {
                $data[$column] = parse_numeric($data[$column] ?? null);
            } catch (InvalidArgumentException $e) {
                throw new ImportTarifException(sprintf(
                    "Baris %d: %s '%s' harus berupa angka (contoh: 1.500 atau 1.500,50)",
                    $line,
                    $headers[$column] ?? $column,
                    Str::limit((string) $data[$column], 50)
                ), 0, $e);
            }
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $headerMapping  Excel header => DB column
     * @param  array<string, mixed>  $raw  DB column => value as typed in Excel
     */
    protected function saveFailed(QueryException $e, string $table, int $line, array $headerMapping, array $raw): ImportTarifException
    {
        $message = (new QueryErrorTranslator(DB::connection('mysql_sik')))
            ->translate($e, $table, $line, $headerMapping, $raw);

        return new ImportTarifException($message, 0, $e);
    }
}
