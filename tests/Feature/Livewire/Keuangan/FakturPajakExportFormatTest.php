<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanFakturPajakAsuransiPerusahaan;
use App\Livewire\Pages\Keuangan\LaporanFakturPajakBPJS;
use App\Livewire\Pages\Keuangan\LaporanFakturPajakUmum;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

/**
 * The export button on the three faktur-pajak reports offers two formats: the
 * report's own layout (FORMAT_RAW, 1) and the layout Coretax imports
 * (FORMAT_CORETAX, 2). Both produce sheets named "Faktur" and "Detail Faktur",
 * so the sheet names cannot tell them apart — the column headers can: Coretax
 * starts every sheet with "Baris", the raw layout with "No. Rawat".
 *
 * The choice has to survive a round trip. exportWithOption() stores it and then
 * only dispatches beginExcelExport; the workbook is built on the next request,
 * from whatever $option holds by then. A workbook in the wrong layout is
 * rejected by Coretax on upload, well after anyone would think to look here.
 *
 * All three components are driven by one data provider because the export
 * code is the same in each; a test per report would be the same test three
 * times.
 */
class FakturPajakExportFormatTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-faktur-pajak.read';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->makeDirectory('excel');
    }

    /**
     * @return array<string, array{0: class-string, 1: int, 2: string}>
     */
    public static function laporanDanFormat(): array
    {
        $cases = [];

        foreach ([
            'BPJS'                => LaporanFakturPajakBPJS::class,
            'Umum'                => LaporanFakturPajakUmum::class,
            'Asuransi/Perusahaan' => LaporanFakturPajakAsuransiPerusahaan::class,
        ] as $nama => $class) {
            $cases["$nama, format laporan"] = [$class, 1, 'No. Rawat'];
            $cases["$nama, format Coretax"] = [$class, 2, 'Baris'];
        }

        return $cases;
    }

    /**
     * The first header cell of every sheet in the workbook, keyed by sheet name.
     *
     * @return array<string, string>
     */
    private function headerPertama(string $isiXlsx): array
    {
        // Written to the faked disk rather than a temp file: on Windows the zip
        // handle is still held when unlink() runs, and the fake cleans up anyway.
        Storage::disk('public')->put('uji-baca.xlsx', $isiXlsx);
        $path = Storage::disk('public')->path('uji-baca.xlsx');

        $reader = new Reader;
        $reader->open($path);

        $headers = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $pertama = (string) ($row->toArray()[0] ?? '');

                // Page headers sit above the column headers; skip past them.
                if (in_array($pertama, ['Baris', 'No. Rawat'], true)) {
                    $headers[$sheet->getName()] = $pertama;

                    break;
                }
            }
        }

        $reader->close();

        return $headers;
    }

    /**
     * @test
     *
     * @dataProvider laporanDanFormat
     */
    public function the_chosen_format_decides_the_layout_of_every_sheet(string $laporan, int $format, string $kolomPertama): void
    {
        $test = Livewire::actingAs($this->petugasWithPermissions([self::PERMISSION], '99999901'))
            ->test($laporan)
            ->call('exportWithOption', $format)
            ->assertSet('option', $format)
            ->assertDispatched('beginExcelExport');

        $test->call('beginExcelExport')->assertFileDownloaded();

        $this->assertSame(
            ['Faktur' => $kolomPertama, 'Detail Faktur' => $kolomPertama],
            $this->headerPertama(base64_decode(data_get($test->effects, 'download.content')))
        );
    }
}
