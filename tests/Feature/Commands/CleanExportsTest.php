<?php

namespace Tests\Feature\Commands;

use App\Models\ExportSession;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CleanExportsTest extends TestCase
{
    private FilesystemAdapter $disk;

    private string $userId = 'test-clean-exports';

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::fake('public');
    }

    protected function tearDown(): void
    {
        ExportSession::query()->where('id_user', $this->userId)->delete();

        parent::tearDown();
    }

    public function test_menghapus_folder_export_selesai_yang_lebih_dari_24_jam(): void
    {
        $session = $this->buatSession('completed', now()->subHours(25));
        $this->buatFolder($session->session_id, ['2026-01-01_export.xlsx'], now()->subHours(25));

        $this->artisan('exports:clean')->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->disk->path($this->folder($session->session_id)));
        $this->assertSame('expired', $session->fresh()->status);
    }

    public function test_export_gagal_yang_lebih_dari_24_jam_juga_dibersihkan(): void
    {
        $session = $this->buatSession('failed', now()->subHours(25));
        $this->buatFolder($session->session_id, ['headers.csv'], now()->subHours(25));

        $this->artisan('exports:clean')->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->disk->path($this->folder($session->session_id)));
        $this->assertSame('expired', $session->fresh()->status);
    }

    public function test_export_yang_belum_24_jam_tidak_disentuh(): void
    {
        $session = $this->buatSession('completed', now()->subHours(23));
        $this->buatFolder($session->session_id, ['2026-01-01_export.xlsx'], now()->subHours(23));

        $this->artisan('exports:clean')->assertExitCode(0);

        $this->assertTrue($this->disk->exists($this->folder($session->session_id).'/2026-01-01_export.xlsx'));
        $this->assertSame('completed', $session->fresh()->status);
    }

    public function test_export_yang_masih_berjalan_tidak_disentuh_walaupun_sudah_lama(): void
    {
        $processing = $this->buatSession('processing', now()->subHours(30));
        $this->buatFolder($processing->session_id, ['headers.csv'], now()->subHours(30));

        $pending = $this->buatSession('pending', now()->subHours(30));

        $this->artisan('exports:clean')->assertExitCode(0);

        $this->assertTrue($this->disk->exists($this->folder($processing->session_id).'/headers.csv'));
        $this->assertSame('processing', $processing->fresh()->status);
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_sisa_folder_pipeline_lama_tanpa_session_ikut_dibersihkan(): void
    {
        $sessionId = Str::uuid()->toString();

        $this->buatFolder($sessionId, [
            'headers.csv',
            '0000000000000001.csv',
            '0000000000000002.csv',
            '2025-01-01_00-00-00_export.xlsx',
        ], now()->subDays(40));

        $this->artisan('exports:clean')->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->disk->path($this->folder($sessionId)));
    }

    public function test_masa_simpan_bisa_diatur_lewat_opsi(): void
    {
        $session = $this->buatSession('completed', now()->subHours(3));
        $this->buatFolder($session->session_id, ['2026-01-01_export.xlsx'], now()->subHours(3));

        $this->artisan('exports:clean', ['--hours' => 2])->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->disk->path($this->folder($session->session_id)));
        $this->assertSame('expired', $session->fresh()->status);
    }

    private function buatSession(string $status, \DateTimeInterface $updatedAt): ExportSession
    {
        $session = ExportSession::query()->create([
            'session_id'  => Str::uuid()->toString(),
            'id_user'     => $this->userId,
            'export_name' => 'buku-besar',
            'status'      => $status,
        ]);

        $session->timestamps = false;
        $session->forceFill(['created_at' => $updatedAt, 'updated_at' => $updatedAt])->save();

        return $session;
    }

    private function buatFolder(string $sessionId, array $files, \DateTimeInterface $modifiedAt): void
    {
        foreach ($files as $file) {
            $path = $this->folder($sessionId).'/'.$file;

            $this->disk->put($path, 'isi');

            touch($this->disk->path($path), $modifiedAt->getTimestamp());
        }
    }

    private function folder(string $sessionId): string
    {
        return "exports/{$this->userId}/{$sessionId}";
    }
}
