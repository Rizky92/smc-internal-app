<?php

namespace Tests\Feature\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use ReflectionMethod;
use RuntimeException;
use Tests\Fixtures\Livewire\EmptyExportHarness;
use Tests\Fixtures\Livewire\MultiSheetExportHarness;
use Tests\Fixtures\Livewire\NamedSheetExportHarness;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;

/**
 * ExcelExportable, inherited by 70 of the 116 page components.
 *
 * This is the Synchronous Export path (see CONTEXT.md): the user presses the
 * button and waits for the workbook to come back as a download in the same
 * interaction. Background Export is a different mechanism entirely and is not
 * covered here.
 *
 * The trait is a two-step handshake on purpose. exportToExcel only puts a
 * "please wait" banner on screen and then dispatches beginExcelExport, so the
 * banner is already painted when the slow part starts. Losing the second
 * dispatch would leave a page that says it is exporting and never does.
 */
class ExcelExportableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // ExcelExport resolves its output directory through the disk, so faking
        // it keeps generated workbooks out of the project's storage directory.
        //
        // The directory has to be created here as well: beginExcelExport() calls
        // File::ensureDirectoryExists() against storage_path() directly rather
        // than against the disk, which is the same place in production but not
        // under a fake.
        Storage::fake('public');
        Storage::disk('public')->makeDirectory('excel');
    }

    /**
     * @test
     */
    public function export_to_excel_memasang_banner_lalu_memulai_ekspor(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class)
            ->dispatch('exportToExcel')
            ->assertDispatched('flash.info')
            ->assertDispatched('beginExcelExport');
    }

    /**
     * A Synchronous Export dies if the user navigates away, so the banner has to
     * say so. The message travels as the flash.info payload, and only becomes a
     * banner on the round trip after — which is why it is asserted on the
     * dispatch rather than in the rendered HTML.
     *
     * @test
     */
    public function banner_yang_dipasang_memberi_tahu_agar_halaman_tidak_ditutup(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class)
            ->call('exportToExcel')
            ->assertDispatched(
                'flash.info',
                fn (string $event, array $params): bool => str_contains($params[0], 'Proses ekspor laporan dimulai')
                    && str_contains($params[0], 'tidak menutup halaman')
            );
    }

    /**
     * The name a report's workbook arrives under: a sortable timestamp, then the
     * component's own class name in snake_case. Users file these by hand, so the
     * shape is part of the contract rather than an implementation detail.
     *
     * @test
     */
    public function nama_berkas_berisi_stempel_waktu_dan_nama_komponen(): void
    {
        $this->travelTo(Carbon::parse('2026-09-16 08:30:00'));

        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class)
            ->call('beginExcelExport')
            ->assertFileDownloaded('20260916_083000_report_harness.xlsx');
    }

    /**
     * A component may name its own file instead. No page component does today —
     * this is the only thing holding that branch of beginExcelExport() to its
     * contract, including the trim and the snake_case conversion.
     *
     * @test
     */
    public function komponen_boleh_menentukan_nama_berkasnya_sendiri(): void
    {
        $this->travelTo(Carbon::parse('2026-09-16 08:30:00'));

        Livewire::actingAs($this->petugasWithPermissions())
            ->test(MultiSheetExportHarness::class)
            ->call('beginExcelExport')
            ->assertFileDownloaded('20260916_083000_laporan_buku_besar.xlsx');
    }

    /**
     * Sheet data may be given as a closure so the rows for a sheet are only
     * built when that sheet is written. Several Keuangan reports rely on it to
     * avoid holding two large result sets at once.
     *
     * @test
     */
    public function data_sheet_berupa_closure_dipanggil_saat_ekspor(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(MultiSheetExportHarness::class)
            ->assertSet('sheetKeduaDibangun', false)
            ->call('beginExcelExport')
            ->assertSet('sheetKeduaDibangun', true);
    }

    /**
     * @test
     */
    public function ekspor_menghasilkan_berkas_yang_tidak_kosong(): void
    {
        $download = Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class)
            ->call('beginExcelExport')
            ->assertFileDownloaded();

        $isi = base64_decode(data_get($download->effects, 'download.content'));

        // "PK" — every .xlsx is a zip archive. A workbook that failed to build
        // comes back as an empty string rather than an error.
        $this->assertStringStartsWith('PK', $isi);
    }

    /**
     * @test
     */
    public function nama_sheet_yang_memuat_seluruh_karakter_terlarang_ditolak(): void
    {
        $this->expectException(RuntimeException::class);

        Livewire::actingAs($this->petugasWithPermissions())
            ->test(NamedSheetExportHarness::class)
            ->set('namaSheet', '\\/?*:[]')
            ->call('beginExcelExport');
    }

    /**
     * One forbidden character is enough, and a lone slash is far and away the
     * likeliest way to get one — a report titled "Ralan/Ranap" is the obvious
     * sheet name to reach for.
     *
     * The guard used to ask Str::containsAll(), satisfied only by a name
     * carrying all seven characters at once, so in practice nothing was ever
     * caught. What lies past it is worse than a malformed workbook: handing that
     * name to xlswriter crashes the PHP process outright (SIGSEGV, exit 139),
     * which under php-fpm is a 502 and a lost worker rather than an error page.
     *
     * The assertion stops at validateSheetNames() for that reason — going one
     * step further would take the test runner down with it if the guard ever
     * regresses.
     *
     * @test
     */
    public function satu_karakter_terlarang_sudah_cukup_untuk_ditolak(): void
    {
        $component = Livewire::actingAs($this->petugasWithPermissions())
            ->test(NamedSheetExportHarness::class)
            ->set('namaSheet', 'Ralan/Ranap')
            ->instance();

        $validate = new ReflectionMethod($component, 'validateSheetNames');
        $validate->setAccessible(true);

        $this->expectException(RuntimeException::class);

        $validate->invoke($component);
    }

    /**
     * And the refusal has to name the sheet, otherwise whoever wrote the report
     * has nothing to go on. The message used to interpolate a boolean into that
     * slot and read "Invalid characters found in sheet: '1'".
     *
     * @test
     */
    public function pesan_penolakan_menyebutkan_nama_sheet_yang_bermasalah(): void
    {
        $component = Livewire::actingAs($this->petugasWithPermissions())
            ->test(NamedSheetExportHarness::class)
            ->set('namaSheet', 'Ralan/Ranap')
            ->instance();

        $validate = new ReflectionMethod($component, 'validateSheetNames');
        $validate->setAccessible(true);

        $this->expectExceptionMessage("Invalid characters found in sheet: 'Ralan/Ranap'");

        $validate->invoke($component);
    }

    /**
     * A component with no sheets used to fail on the first one with "Undefined
     * array key 0" — an error page for whoever pressed the button. It now tells
     * the user there is nothing to export, and produces no file.
     *
     * @test
     */
    public function ekspor_tanpa_sheet_memberi_tahu_user_alih_alih_crash(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(EmptyExportHarness::class)
            ->call('beginExcelExport')
            ->assertOk()
            ->assertDispatched(
                'flash.error',
                fn (string $event, array $params): bool => $params[0] === 'Tidak ada data yang dapat diekspor dari halaman ini.'
            )
            ->assertNoFileDownloaded();
    }

    /**
     * The guard is for no sheets, not for no rows. A sheet whose rows are empty
     * is an ordinary result — a period with no transactions — and still exports,
     * as a workbook holding just its headers.
     *
     * @test
     */
    public function sheet_tanpa_baris_tetap_diekspor(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(NamedSheetExportHarness::class)
            ->set('namaSheet', 'Kosong')
            ->call('beginExcelExport')
            ->assertNotDispatched('flash.error')
            ->assertFileDownloaded();
    }
}
