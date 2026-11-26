<?php

namespace App\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a MySQL/MariaDB error from an Excel import row into a message that
 * names the Excel header and the value the user typed. Matched on the driver
 * error code (errorInfo[1]); column details come from information_schema.
 */
class QueryErrorTranslator
{
    private const MAX_VALUE_LENGTH = 50;

    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  array<string, string>  $headerMapping  Excel header => DB column
     * @param  array<string, mixed>  $raw  DB column => value as typed in Excel
     */
    public function translate(QueryException $e, string $table, int $line, array $headerMapping, array $raw): string
    {
        $code = (int) ($e->errorInfo[1] ?? 0);
        $message = (string) ($e->errorInfo[2] ?? '');
        $headers = array_flip($headerMapping);

        $header = fn (string $column): string => $headers[$column] ?? $column;
        $quote = fn ($value): string => "'".Str::limit((string) $value, self::MAX_VALUE_LENGTH)."'";

        $column = $this->column($code, $message);

        $text = $column === null ? null : $this->describe($code, $table, $column, $message, $header, $raw, $quote);

        return $text === null
            ? "Baris {$line}: Gagal menyimpan data ke database. Coba ulangi import; jika masih gagal, hubungi tim IT. (kode {$code})"
            : "Baris {$line}: {$text} (kode {$code})";
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function describe(int $code, string $table, string $column, string $message, callable $header, array $raw, callable $quote): ?string
    {
        switch ($code) {
            case 1265:
                return $this->invalidValue($table, $column, $header($column), $raw, $quote);

            case 1406:
                return sprintf(
                    '%s %s melebihi %s karakter',
                    $header($column),
                    $quote($raw[$column] ?? ''),
                    $this->columnInfo($table, $column)['CHARACTER_MAXIMUM_LENGTH'] ?? '?'
                );

            case 1048:
                return "{$header($column)} wajib diisi";

            case 1366:
                return "{$header($column)} {$quote($raw[$column] ?? $this->quotedValue($message))} harus berupa angka";

            case 1452:
                return "{$header($column)} {$quote($raw[$column] ?? '')} tidak ditemukan";

            case 1062:
                return $this->duplicate($table, $column, $message, $header, $raw, $quote);

            default:
                return null;
        }
    }

    /**
     * The column (or, for 1062, the key name) the error is about.
     */
    private function column(int $code, string $message): ?string
    {
        $patterns = [
            1265 => "/for column '([^']+)'/",
            1406 => "/for column '([^']+)'/",
            1048 => "/Column '([^']+)'/",
            // MariaDB: `db`.`table`.`col`; MySQL 8: 'col'
            1366 => "/for column (?:`[^`]+`\\.`[^`]+`\\.`([^`]+)`|'([^']+)')/",
            1452 => '/FOREIGN KEY \(`([^`]+)`/',
            // MySQL 8 prefixes the table: 'table.key'
            1062 => "/for key '(?:[^'.]+\\.)?([^']+)'/",
        ];

        $pattern = $patterns[$code] ?? null;

        if ($pattern === null || ! preg_match($pattern, $message, $m)) {
            return null;
        }

        return ($m[2] ?? '') !== '' ? $m[2] : $m[1];
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function invalidValue(string $table, string $column, string $header, array $raw, callable $quote): string
    {
        $choices = $this->choices($table, $column);
        $known = array_key_exists($column, $raw);
        $blank = $known && blank(trim(str_replace(["\u{00A0}", "\u{202F}"], ' ', (string) $raw[$column])));

        if ($choices === null) {
            return $blank ? "{$header} wajib diisi" : "{$header}".($known ? " {$quote($raw[$column])}" : '').' format tidak valid';
        }

        $list = 'Pilihan: '.implode(', ', $choices);

        if ($blank) {
            return "{$header} wajib diisi. {$list}";
        }

        return "{$header}".($known ? " {$quote($raw[$column])}" : '')." tidak valid. {$list}";
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function duplicate(string $table, string $key, string $message, callable $header, array $raw, callable $quote): string
    {
        $columns = $this->keyColumns($table, $key);

        if ($columns === [] || array_diff($columns, array_keys($raw)) !== []) {
            return "data {$quote($this->quotedValue($message))} sudah ada (duplikat)";
        }

        if (count($columns) === 1) {
            return "{$header($columns[0])} {$quote($raw[$columns[0]])} sudah ada";
        }

        return sprintf(
            'kombinasi %s (%s) sudah ada',
            implode(', ', array_map($header, $columns)),
            implode(', ', array_map(fn ($c) => $quote($raw[$c]), $columns))
        );
    }

    /**
     * The first quoted value in a driver message, e.g. "Duplicate entry 'X' ...".
     */
    private function quotedValue(string $message): string
    {
        return preg_match("/'((?:[^']|'')*)'/", $message, $m) ? $m[1] : '';
    }

    /**
     * @return list<string>|null  enum/set choices, or null for any other type
     */
    private function choices(string $table, string $column): ?array
    {
        $type = (string) ($this->columnInfo($table, $column)['COLUMN_TYPE'] ?? '');

        if (! preg_match('/^(?:enum|set)\((.*)\)$/i', $type, $m)) {
            return null;
        }

        preg_match_all("/'((?:[^']|'')*)'/", $m[1], $values);

        return array_map(fn ($v) => str_replace("''", "'", $v), $values[1]);
    }

    /**
     * @return array<string, mixed>
     */
    private function columnInfo(string $table, string $column): array
    {
        try {
            $row = $this->connection->selectOne(
                'select COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH from information_schema.COLUMNS where TABLE_SCHEMA = DATABASE() and TABLE_NAME = ? and COLUMN_NAME = ?',
                [$table, $column]
            );
        } catch (Throwable $e) {
            return [];
        }

        return (array) $row;
    }

    /**
     * @return list<string>
     */
    private function keyColumns(string $table, string $key): array
    {
        try {
            return $this->connection->table('information_schema.STATISTICS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', $table)
                ->where('INDEX_NAME', $key)
                ->orderBy('SEQ_IN_INDEX')
                ->pluck('COLUMN_NAME')
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }
}
