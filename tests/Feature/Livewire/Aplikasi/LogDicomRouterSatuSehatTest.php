<?php

namespace Tests\Feature\Livewire\Aplikasi;

use App\Livewire\Pages\Aplikasi\LogDicomRouterSatuSehat;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the DICOM-to-Satu-Sehat delivery log.
 *
 * ringkasanPengiriman() reruns the same filtered query with a count/sum
 * instead of the row select - a filter that narrows the list but not the
 * scope shared by both would show one number in the summary and a
 * different set of rows in the table beneath it, so this suite checks the
 * summary counts, not just that the page renders.
 */
class LogDicomRouterSatuSehatTest extends TestCase
{
    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('dicom_router_webhook_logs')->where('signature', 'like', 'uji-%')->delete();

        parent::tearDown();
    }

    private function log(string $signature, string $accessionNumber, string $stage, bool $status, string $waktu): void
    {
        DB::connection('mysql_smc')->table('dicom_router_webhook_logs')->insert([
            'signature'        => $signature,
            'accession_number' => $accessionNumber,
            'stage'            => $stage,
            'status'           => $status,
            'payload'          => '{}',
            'created_at'       => $waktu,
            'updated_at'       => $waktu,
        ]);
    }

    private function seedLogs(): void
    {
        $this->log('uji-1', 'UJI0001', 'dicom_sent', true, self::AWAL.' 08:00:00');
        $this->log('uji-2', 'UJI0002', 'dicom_sent', true, self::AWAL.' 09:00:00');
        $this->log('uji-3', 'UJI0003', 'dicom_send_failed', false, self::AWAL.' 10:00:00');
        // Outside the period.
        $this->log('uji-4', 'UJI0004', 'dicom_sent', true, '2026-04-05 08:00:00');
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.log-dicom-router-satu-sehat.read'], '99999901');

        return Livewire::actingAs($petugas)
            ->test(LogDicomRouterSatuSehat::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties');
    }

    /**
     * @test
     */
    public function summarises_only_deliveries_inside_the_period(): void
    {
        $this->seedLogs();

        $ringkasan = $this->report()->get('ringkasanPengiriman');

        $this->assertSame(3, $ringkasan['total']);
        $this->assertSame(2, $ringkasan['berhasil']);
        $this->assertSame(1, $ringkasan['gagal']);
    }

    /**
     * @test
     *
     * Narrowing by stage has to shrink the summary the same way it shrinks
     * the table - both read through the same scope.
     */
    public function narrows_the_summary_by_stage(): void
    {
        $this->seedLogs();

        $petugas = $this->petugasWithPermissions(['aplikasi.log-dicom-router-satu-sehat.read'], '99999901');

        $ringkasan = Livewire::actingAs($petugas)
            ->test(LogDicomRouterSatuSehat::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->set('stage', 'dicom_send_failed')
            ->call('loadProperties')
            ->get('ringkasanPengiriman');

        $this->assertSame(1, $ringkasan['total']);
        $this->assertSame(0, $ringkasan['berhasil']);
        $this->assertSame(1, $ringkasan['gagal']);
    }

    /**
     * @test
     */
    public function lists_only_deliveries_inside_the_period(): void
    {
        $this->seedLogs();

        $this->report()
            ->assertSee('UJI0001')
            ->assertSee('UJI0003')
            ->assertDontSee('UJI0004');
    }
}
