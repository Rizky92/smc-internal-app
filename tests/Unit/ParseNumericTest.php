<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ParseNumericTest extends TestCase
{
    /**
     * Formats the tarif importers accept today, whose results must not change.
     *
     * @return array<string, array{mixed, float}>
     */
    public static function acceptedFormats(): array
    {
        return [
            'rupiah dengan titik ribuan' => ['Rp 1.500', 1500.0],
            'rupiah tanpa spasi + ,-'    => ['Rp1.500,-', 1500.0],
            'digit polos'                => ['1500', 1500.0],
            'nol'                        => ['0', 0.0],
            'int dari Excel'             => [1500, 1500.0],
            'float dari Excel'           => [1500.75, 1500.75],
            'strip'                      => ['-', 0.0],
            'kosong'                     => ['', 0.0],
            'null'                       => [null, 0.0],
            'titik ribuan bertingkat'    => ['1.500.000', 1500000.0],
            'koma desimal 1 digit'       => ['1,5', 1.5],
            'koma desimal 2 digit'       => ['1,50', 1.5],
            'titik desimal 1 digit'      => ['1.5', 1.5],
            'titik desimal 2 digit'      => ['1.50', 1.5],
            'ribuan + koma desimal'      => ['1.500,50', 1500.5],
            'spasi ribuan'               => ['1 500', 1500.0],
        ];
    }

    /**
     * @dataProvider acceptedFormats
     *
     * @param  mixed  $input
     */
    public function test_accepted_format($input, float $expected): void
    {
        $this->assertSame($expected, parse_numeric($input));
    }

    /**
     * @return array<string, array{mixed, float}>
     */
    public static function tightenedFormats(): array
    {
        return [
            'titik + 3 digit adalah ribuan' => ['1.500', 1500.0],
            'Rp huruf kecil'                => ['rp 1.500', 1500.0],
            'RP huruf besar'                => ['RP1.500', 1500.0],
            'spasi ribuan NBSP'             => ["1\u{00A0}500", 1500.0],
            'spasi ribuan U+202F'           => ["1\u{202F}500", 1500.0],
            'strip dengan spasi'            => [' - ', 0.0],
            'hanya spasi'                   => ['   ', 0.0],
            'hanya NBSP'                    => ["\u{00A0}", 0.0],
        ];
    }

    /**
     * @dataProvider tightenedFormats
     *
     * @param  mixed  $input
     */
    public function test_tightened_format($input, float $expected): void
    {
        $this->assertSame($expected, parse_numeric($input));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function rejectedFormats(): array
    {
        return [
            'koma + 3 digit ambigu'      => ['1,500'],
            'koma + 3 digit ambigu (2)'  => ['12,500'],
            'format US'                  => ['1,500.50'],
            'pemisah ribuan campur'      => ['1 500.000'],
            'grup ribuan salah'          => ['1.50.0'],
            'grup ribuan salah (2)'      => ['1.5.000'],
            'huruf'                      => ['abc'],
            'angka + huruf'              => ['12abc'],
            'satuan rb'                  => ['1.500 rb'],
            'strip ganda'                => ['--'],
            'rentang'                    => ['1-2'],
            'hanya Rp'                   => ['Rp'],
            'negatif teks'               => ['-1500'],
            'negatif dengan titik'       => ['-1.500'],
            'negatif int dari Excel'     => [-1500],
            'negatif float dari Excel'   => [-1.5],
        ];
    }

    /**
     * @dataProvider rejectedFormats
     *
     * @param  mixed  $input
     */
    public function test_rejected_format($input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        parse_numeric($input);
    }
}
