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
}
