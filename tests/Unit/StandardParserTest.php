<?php

namespace Tests\Unit;

use App\Support\Mutu\StandardParser;
use PHPUnit\Framework\TestCase;

class StandardParserTest extends TestCase
{
    /**
     * @return array<string, array{string|null, float|null}>
     */
    public static function standards(): array
    {
        return [
            'persen polos'                 => ['80%', 80.0],
            'seratus persen'               => ['100%', 100.0],
            'tanpa tanda persen'           => ['85', 85.0],
            'dengan operator dan spasi'    => ['≥ 85 %', 85.0],
            'operator ascii'               => ['<= 5%', 5.0],
            'koma desimal'                 => ['97,5%', 97.5],
            'titik desimal'                => ['97.5 %', 97.5],
            'teks di depan'                => ['Minimal 90%', 90.0],
            'angka pertama yang dipakai'   => ['80% - 90%', 80.0],
            'nol'                          => ['0%', 0.0],
            'spasi di sekitar'             => ['  75 % ', 75.0],
            'tanpa angka'                  => ['tidak ada', null],
            'tanda strip'                  => ['-', null],
            'string kosong'                => ['', null],
            'null'                         => [null, null],
        ];
    }

    /**
     * @dataProvider standards
     */
    public function test_parse(?string $standard, ?float $expected): void
    {
        $this->assertSame($expected, StandardParser::parse($standard));
    }

    /**
     * Arah hanya diambil bila ditulis eksplisit; selain itu null agar dipastikan Komite Mutu.
     *
     * @return array<string, array{string|null, string|null}>
     */
    public static function operators(): array
    {
        return [
            'simbol ≥'                        => ['≥ 85%', 'gte'],
            'simbol ≥ tanpa spasi'            => ['≥85%', 'gte'],
            'ascii >='                        => ['>= 85 %', 'gte'],
            'kata minimal'                    => ['Minimal 90%', 'gte'],
            'kata minimal huruf besar'        => ['MINIMAL 90 %', 'gte'],
            'simbol ≤'                        => ['≤ 5%', 'lte'],
            'ascii <='                        => ['<=5%', 'lte'],
            'kata maksimal'                   => ['maksimal 5%', 'lte'],
            'kata maksimal di tengah'         => ['Angka penundaan maksimal 5 %', 'lte'],
            'angka saja'                      => ['80%', null],
            'seratus persen'                  => ['100%', null],
            'lebih dari tanpa sama dengan'    => ['> 80%', null],
            'kurang dari tanpa sama dengan'   => ['< 5%', null],
            'singkatan min tidak diakui'      => ['min 80%', null],
            'kata yang memuat minimal'        => ['minimalis 80%', null],
            'arah bertentangan'               => ['minimal 80%, maksimal 95%', null],
            'rentang'                         => ['80% - 90%', null],
            'tanpa angka'                     => ['tidak ada', null],
            'string kosong'                   => ['', null],
            'null'                            => [null, null],
        ];
    }

    /**
     * @dataProvider operators
     */
    public function test_parse_operator(?string $standard, ?string $expected): void
    {
        $this->assertSame($expected, StandardParser::parseOperator($standard));
    }
}
