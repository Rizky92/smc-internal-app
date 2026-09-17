<?php

namespace Tests\Feature;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Pages\Keuangan\LabaRugiRekeningPerPeriode;
use App\Livewire\Pages\Perawatan\LaporanPasienRanap;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;
use Throwable;

/**
 * Every report screen, driven through what a user does on it, with its queries
 * actually running.
 *
 * RouteSweepTest proves each page serves, but a report mounts deferred, so that
 * sweep never executes a single report query: the table is empty by design
 * until wire:init asks for data. This one asks. Against the structure-only
 * sik_test schema the result sets are empty, which is the point — what is
 * under test is that the SQL is valid for the schema it runs on: a renamed
 * Khanza column, a sort key that is not a column, a search over a join that no
 * longer exists, an export query nobody has run since it was written.
 *
 * Per component, in order: mount, load, search, sort by every sortable column
 * the rendered table offers, turn a page, reset the filters, and — where the
 * component exports — build the workbook. Each step is a separate request, the
 * way the browser sends them, and a failure names the step.
 *
 * Every statement run along the way is also checked for a database named
 * literally rather than taken from the connection. Such a query still works on
 * a developer machine, reading that developer's real data instead of the test
 * schema, and breaks on any server whose databases are named differently. It
 * is invisible everywhere except here.
 *
 * Components are discovered from app/Livewire/Pages: anything with LiveTable
 * that is not a modal and mounts without arguments. A report added later is
 * swept without anyone remembering this file exists.
 */
class ReportSweepTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string<Component>}>
     */
    public static function reports(): array
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $cases = [];

        foreach (Finder::create()->files()->in(app_path('Livewire/Pages'))->name('*.php')->sortByName() as $file) {
            $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $class = 'App\\Livewire\\Pages\\'.$relative;

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $traits = class_uses_recursive($class);

            if ($reflection->isAbstract()
                || ! $reflection->isSubclassOf(Component::class)
                || ! in_array(LiveTable::class, $traits, true)
                || in_array(DeferredModal::class, $traits, true)
                || ($reflection->hasMethod('mount') && $reflection->getMethod('mount')->getNumberOfRequiredParameters() > 0)) {
                continue;
            }

            $cases[str_replace('\\', '/', $relative)] = [$class];
        }

        return $cases;
    }

    /**
     * Steps known to fail, skipped so they do not mask regressions elsewhere.
     *
     * Quarantine is per step, not per component: the other steps of the same
     * report still run, and the test is reported as incomplete rather than
     * passing, so a quarantined step stays visible in every run. Each entry says
     * why. Take it out as soon as the reason is gone — a quarantined step that
     * starts passing fails the test, so a fix cannot go unnoticed.
     *
     * @return array<class-string, array<string, string>>
     */
    private static function quarantined(): array
    {
        return [
            LaporanPasienRanap::class => [
                'muat data (loadProperties)' => 'SKEMA: PasienRanap membaca VIEW mysql_smc.laporan_pasien_ranap yang dibuat di luar migrasi, sehingga smc_test (dibangun dari migrasi) tidak memilikinya. Definisinya di smc_old menulis `sik`. secara literal di 64 tempat.',
                'reset filter'               => 'SKEMA: resetFilters() mengangkat deferral dan menjalankan query yang sama; lihat "muat data".',
                'ekspor Excel'               => 'SKEMA: ekspor membaca VIEW yang sama; lihat "muat data".',
            ],
            LabaRugiRekeningPerPeriode::class => [
                'muat data (loadProperties)' => 'DATA: mapToGroups() tidak membuat grup D/K bila tidak ada rekening tipe R dengan balance itu, dan view mem-foreach null. sik_test tidak berisi rekening; produksi selalu punya keduanya. Diuji dengan ledger lengkap di LabaRugiRekeningPerPeriodeTest.',
                'reset filter'               => 'DATA: resetFilters() mengangkat deferral dan merender tabel yang sama; lihat "muat data".',
            ],
        ];
    }

    /**
     * A database named in SQL literally, as `sik`.`table` or sik.table, rather
     * than through the connection. The configured test schemas end in _test, so
     * they never match.
     */
    private const HARDCODED_DATABASE = '/(?<![A-Za-z0-9_`])`?(sik|smc|smc_old)`?\.`?[A-Za-z_]/i';

    /**
     * @test
     *
     * @dataProvider reports
     */
    public function report_survives_what_a_user_does_on_it(string $class): void
    {
        $this->quarantine = self::quarantined()[$class] ?? [];
        $this->skipped = [];

        Storage::fake('public');
        Storage::disk('public')->makeDirectory('excel');

        $hardcoded = [];

        DB::listen(function (QueryExecuted $query) use (&$hardcoded) {
            if (preg_match(self::HARDCODED_DATABASE, $query->sql)) {
                $hardcoded[] = "[{$query->connectionName}] ".preg_replace('/\s+/', ' ', $query->sql);
            }
        });

        $traits = class_uses_recursive($class);
        $superadmin = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        $test = $this->step('mount', fn () => Livewire::actingAs($superadmin)->test($class)->assertOk());

        if (in_array(DeferredLoading::class, $traits, true)) {
            $this->step('muat data (loadProperties)', fn () => $test->call('loadProperties')->assertOk());
        }

        $this->step('cari', fn () => $test->set('cari', 'uji sweep')->assertOk());

        preg_match_all("/wire:click=\"sortBy\('([^']+)'/", $test->html(), $matches);

        foreach (array_unique($matches[1]) as $column) {
            $this->step("urutkan kolom [$column]", fn () => $test->call('sortBy', $column, null)->assertOk());
        }

        $this->step('pindah halaman', fn () => $test->call('gotoPage', 2)->assertOk());
        $this->step('reset filter', fn () => $test->call('resetFilters')->assertOk());

        // Only where the page actually offers it. Several components use the
        // trait with an empty dataPerSheet() and no button; that export is not
        // reachable, and failing it would be noise.
        $html = $test->html();

        if (in_array(ExcelExportable::class, $traits, true)
            && (str_contains($html, 'wire:click.prevent="exportToExcel"') || str_contains($html, 'exportWithOption(1)'))) {
            $this->step('ekspor Excel', fn () => $test->call('beginExcelExport')->assertOk());
        }

        $this->assertSame([], array_values(array_unique($hardcoded)), sprintf(
            "%s menjalankan SQL dengan nama database yang ditulis mati. Di mesin developer query ini membaca database asli, bukan skema test; di server dengan nama database lain, query ini gagal:\n  - %s",
            class_basename($class),
            implode("\n  - ", array_unique($hardcoded))
        ));

        $stale = array_diff(array_keys($this->quarantine), array_keys($this->skipped));

        // A quarantined step the component never reached is stale: the step was
        // renamed, or the button it depended on is gone.
        $this->assertSame([], array_values($stale), 'Karantina menyebut langkah yang tidak dijalankan komponen ini: '.implode(', ', $stale));

        if ($this->skipped !== []) {
            $this->markTestIncomplete(implode("\n", array_map(
                fn ($step, $reason) => "Langkah \"$step\" dikarantina — $reason",
                array_keys($this->skipped),
                $this->skipped
            )));
        }
    }

    /** @var array<string, string> */
    private array $quarantine = [];

    /** @var array<string, string> */
    private array $skipped = [];

    /**
     * Run one step, and if it fails, say which one.
     *
     * A quarantined step is still run: it is expected to fail, and if it does
     * not, the quarantine entry is out of date and the test says so.
     *
     * @template T
     *
     * @param  callable(): T  $step
     * @return T|null
     */
    private function step(string $name, callable $step)
    {
        $reason = $this->quarantine[$name] ?? null;

        try {
            $result = $step();
        } catch (Throwable $e) {
            if ($reason !== null) {
                $this->skipped[$name] = $reason;

                return null;
            }

            $this->fail(sprintf("Langkah \"%s\" gagal: %s: %s\n%s:%d", $name, class_basename($e), $e->getMessage(), $e->getFile(), $e->getLine()));
        }

        if ($reason !== null) {
            $this->fail("Langkah \"$name\" dikarantina tetapi ternyata berhasil. Hapus entrinya dari quarantined(): $reason");
        }

        return $result;
    }
}
