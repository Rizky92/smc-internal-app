<?php

namespace Tests\Feature\Livewire\Concerns;

use App\Livewire\Concerns\MenuTracker;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Exceptions\BaseException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;
use Throwable;

/**
 * MenuTracker, inherited by 80 of the 116 page components.
 *
 * It writes one row to mysql_smc.trackermenu every time a page is opened, which
 * is how the hospital knows which reports are actually used. The trait disables
 * itself under PHPUnit, and that switch cuts both ways: it keeps 446 tests from
 * filling the audit table, and it means no other test in this suite ever
 * executes a single line of what the trait does in production. Both halves are
 * covered here.
 */
class MenuTrackerTest extends TestCase
{
    /**
     * The switch that keeps the rest of the suite out of the audit table. If it
     * is ever removed, every test that mounts a page starts writing rows into
     * smc_test.trackermenu and this is what says so.
     *
     * @test
     */
    public function mount_saat_pengujian_tidak_menulis_ke_trackermenu(): void
    {
        $sebelum = DB::connection('mysql_smc')->table('trackermenu')->count();

        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class)
            ->assertOk();

        $this->assertSame(
            $sebelum,
            DB::connection('mysql_smc')->table('trackermenu')->count(),
            'MenuTracker menulis baris audit saat pengujian; penjaga app()->runningUnitTests() di recordVisitor() hilang.'
        );
    }

    /**
     * The blind spot that switch creates, closed by a sweep.
     *
     * In production the first thing recordVisitor() does is build the menu path
     * from Breadcrumbs::generate(). With 'invalid-named-breadcrumb-exception'
     * turned on in config/breadcrumbs.php, a route with no breadcrumb defined
     * for it throws there — on mount, before the page renders anything. Adding a
     * page and forgetting its breadcrumb is therefore not a missing trail, it is
     * a screen that will not open at all.
     *
     * RouteSweepTest cannot catch this: it visits the same pages, but the tracker
     * is switched off while it does, so the call never runs. The cases are read
     * from the router so a route added later is covered without anyone
     * remembering this file exists.
     *
     * @dataProvider trackedRoutes
     *
     * @test
     */
    public function setiap_rute_halaman_punya_breadcrumb_agar_tracker_tidak_meledak(string $routeName): void
    {
        try {
            $trail = Breadcrumbs::generate($routeName);
        } catch (BaseException $e) {
            $this->fail(sprintf(
                "Rute [%s] tidak punya breadcrumb. MenuTracker::recordVisitor() akan melempar %s saat halaman ini dibuka di produksi.\n%s",
                $routeName,
                class_basename($e),
                $e->getMessage()
            ));
        }

        $this->assertNotEmpty(
            $trail,
            sprintf('Breadcrumb rute [%s] kosong, sehingga baris trackermenu-nya tercatat tanpa nama menu.', $routeName)
        );
    }

    /**
     * Named GET routes served by a component that actually uses MenuTracker.
     *
     * The trait is what calls Breadcrumbs::generate(), so a page without it —
     * the print layouts, for instance — has no reason to own a breadcrumb, and
     * neither do the routes the impersonation package registers. Deriving the
     * list from the components rather than from the route table keeps the sweep
     * pointed at the pages the question applies to.
     *
     * @return array<string, array{0: string}>
     */
    public static function trackedRoutes(): array
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $cases = [];

        foreach ($app['router']->getRoutes() as $route) {
            $name = $route->getName();
            $uses = $route->getAction('uses');

            if ($name === null || ! in_array('GET', $route->methods(), true)) {
                continue;
            }

            if (str_contains($route->uri(), '{') || ! is_string($uses)) {
                continue;
            }

            $class = ltrim(explode('@', $uses)[0], '\\');

            if (! class_exists($class) || ! in_array(MenuTracker::class, class_uses_recursive($class), true)) {
                continue;
            }

            $cases[$name] = [$name];
        }

        return $cases;
    }

    /**
     * Impersonation is the other early return. An admin looking at the
     * application through someone else's account must not leave rows that say
     * that person opened the page.
     *
     * @test
     */
    public function penjaga_impersonasi_ada_dan_terbaca_lebih_dulu(): void
    {
        $source = file_get_contents(app_path('Livewire/Concerns/MenuTracker.php'));

        $this->assertMatchesRegularExpression(
            '/if \(\s*app\(\'impersonate\'\)->isImpersonating\(\).*?\)\s*\{\s*return;/s',
            $source,
            'Penjaga impersonasi di MenuTracker::recordVisitor() hilang; sesi impersonasi akan tercatat atas nama user yang ditiru.'
        );
    }

    /**
     * @test
     */
    public function tidak_ada_pengecualian_saat_komponen_mount_tanpa_konteks_rute(): void
    {
        try {
            Livewire::actingAs($this->petugasWithPermissions())
                ->test(ReportHarness::class)
                ->assertOk();
        } catch (Throwable $e) {
            $this->fail('MenuTracker menggagalkan mount di luar konteks rute: '.$e->getMessage());
        }

        $this->addToAssertionCount(1);
    }
}
