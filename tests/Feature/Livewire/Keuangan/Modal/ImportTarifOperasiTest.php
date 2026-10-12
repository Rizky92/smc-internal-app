<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifOperasiJob;
use App\Livewire\Pages\Keuangan\Modal\ImportTarifOperasi;
use App\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: kicking off a background import of tarif operasi from an uploaded
 * spreadsheet.
 *
 * importData() doesn't touch the database itself - it queues
 * ImportTarifOperasiJob and returns immediately, so this suite's job is to
 * pin that the two guards (permission, file present) run before the job is
 * ever queued, and that a valid submission queues exactly one job and
 * notifies the uploader, rather than to exercise the import itself.
 */
class ImportTarifOperasiTest extends TestCase
{
    /**
     * @test
     */
    public function refuses_to_import_without_permission(): void
    {
        Queue::fake();

        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(ImportTarifOperasi::class)
            ->set('fileImport', UploadedFile::fake()->create('tarif-operasi.xlsx', 10))
            ->call('importData')
            ->assertDispatched('data-denied');

        Queue::assertNotPushed(ImportTarifOperasiJob::class);
    }

    /**
     * @test
     */
    public function refuses_to_import_without_a_file(): void
    {
        Queue::fake();

        $petugas = $this->petugasWithPermissions(['keuangan.tarif-operasi.create'], '99999901');

        Livewire::actingAs($petugas)
            ->test(ImportTarifOperasi::class)
            ->call('importData')
            ->assertDispatched('flash.error', 'File import belum diunggah.');

        Queue::assertNotPushed(ImportTarifOperasiJob::class);
    }

    /**
     * @test
     */
    public function queues_the_import_and_notifies_the_uploader(): void
    {
        Queue::fake();
        NotificationFacade::fake();

        $petugas = $this->petugasWithPermissions(['keuangan.tarif-operasi.create'], '99999901');

        Livewire::actingAs($petugas)
            ->test(ImportTarifOperasi::class)
            ->set('fileImport', UploadedFile::fake()->create('tarif-operasi.xlsx', 10))
            ->call('importData')
            ->assertDispatched('data-saved')
            ->assertSet('fileImport', null);

        Queue::assertPushed(ImportTarifOperasiJob::class, 1);
        NotificationFacade::assertSentTo($petugas, Notification::class);
    }
}
