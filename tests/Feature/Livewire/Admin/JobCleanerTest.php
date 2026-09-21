<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Pages\Admin\JobCleaner;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the superadmin-only "Job Cleaner" tool - a job count and a button
 * that runs `queue:clear`.
 *
 * QUEUE_CONNECTION is forced to "sync" for the whole test suite
 * (phpunit.xml), and Laravel's SyncQueue does not implement ClearableQueue,
 * so `Artisan::call('queue:clear')` silently clears nothing under the
 * suite's default queue connection. cleanJobs() below therefore switches
 * queue.default to "database" for the duration of the test, the same
 * connection production actually runs under (see the app's own .env), so the
 * clear is exercised for real rather than trivially passing because there
 * was nothing to clear.
 */
class JobCleanerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('jobs')->delete();

        parent::tearDown();
    }

    /**
     * "default" matches config('queue.connections.database.queue') -
     * queue:clear only clears the queue name it resolves to for the
     * connection, not every row in the table.
     */
    private function seedJob(): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default', 'payload' => '{}', 'attempts' => 0,
            'available_at' => time(), 'created_at' => time(),
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        return Livewire::actingAs($petugas)->test(JobCleaner::class);
    }

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $this->report()->assertOk();
    }

    /**
     * @test
     */
    public function reports_the_current_number_of_queued_jobs(): void
    {
        $this->seedJob();
        $this->seedJob();

        $this->assertSame(2, $this->report()->instance()->jobs);
    }

    /**
     * @test
     */
    public function clean_jobs_empties_the_jobs_table(): void
    {
        config(['queue.default' => 'database']);

        $this->seedJob();

        $this->report()->call('cleanJobs');

        $this->assertSame(0, DB::table('jobs')->count());
    }
}
