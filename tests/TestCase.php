<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\CreatesPasien;
use Tests\Concerns\CreatesPetugas;

/**
 * Neither connection is wrapped in a transaction, and that is deliberate.
 *
 * SIAP reads across the connection boundary in raw SQL — scopes like
 * User::scopeTampilkanYangMemilikiHakAkses() run on mysql_sik but name mysql_smc's
 * tables by schema, because there is no foreign key to join on. Transactional
 * isolation breaks that in two separate ways:
 *
 *   1. A query on one PDO connection cannot see another connection's uncommitted
 *      rows, so authorisation written inside an open mysql_smc transaction is
 *      invisible to the mysql_sik query meant to find it.
 *   2. Under REPEATABLE READ, an open mysql_sik transaction fixes its snapshot at
 *      its first read. Anything mysql_smc commits afterwards stays invisible even
 *      once committed.
 *
 * Either way the scope returns nothing and the test fails for a reason that has
 * nothing to do with the scope. Fixtures are therefore committed, exactly as the
 * application's own data is, and removed by hand afterwards.
 *
 * RefreshDatabase is not an option either: SIAP does not own SIMRS Khanza's
 * schema, so the 1,203 tables in sik_test are a structure-only copy that has to
 * survive the suite.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use CreatesPasien;
    use CreatesPetugas;

    protected function setUp(): void
    {
        parent::setUp();

        // A previous run that died mid-test can leave rows behind. Start clean
        // rather than inheriting them.
        $this->deleteAuthorisationFixtures();
        $this->deletePasienFixtures();
        $this->deletePetugasFixtures();
    }

    protected function tearDown(): void
    {
        $this->deleteAuthorisationFixtures();
        $this->deletePasienFixtures();
        $this->deletePetugasFixtures();

        parent::tearDown();
    }

    /**
     * Children before parents: the pivot tables carry foreign keys into
     * `permissions` and `roles`.
     */
    private function deleteAuthorisationFixtures(): void
    {
        $smc = DB::connection('mysql_smc');
        $tables = config('permission.table_names');

        foreach ([
            $tables['model_has_permissions'],
            $tables['model_has_roles'],
            $tables['role_has_permissions'],
            $tables['permissions'],
            $tables['roles'],
        ] as $table) {
            $smc->table($table)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
