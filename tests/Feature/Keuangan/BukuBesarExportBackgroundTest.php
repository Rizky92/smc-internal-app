<?php

namespace Tests\Feature\Keuangan;

use App\Livewire\Pages\Keuangan\BukuBesar;
use App\Models\Aplikasi\User;
use App\Models\ExportSession;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BukuBesarExportBackgroundTest extends TestCase
{
    private string $nik = 'test-export-bg';

    protected function tearDown(): void
    {
        ExportSession::query()->where('id_user', $this->nik)->delete();

        parent::tearDown();
    }

    public function test_dispatch_gagal_tidak_mengunci_export_berikutnya(): void
    {
        // Insert ke tabel jobs gagal, seperti dispatch yang crash pada 2026-09-23.
        config([
            'queue.default'                    => 'database',
            'queue.connections.database.table' => 'tabel_jobs_tidak_ada',
        ]);

        Storage::fake('public');
        Notification::fake();

        $user = (new User)->forceFill(['nik' => $this->nik]);

        $component = Livewire::actingAs($user)->test(BukuBesar::class);

        try {
            $component->call('exportToBackground');
        } catch (\Throwable $e) {
            // Percobaan pertama boleh gagal; yang diuji adalah percobaan kedua.
        }

        config(['queue.default' => 'sync', 'queue.connections.database.table' => 'jobs']);

        // Percobaan kedua harus berjalan, bukan ditolak sebagai "sedang berjalan".
        $component->call('exportToBackground')->assertNotEmitted('flash.error');
    }
}
