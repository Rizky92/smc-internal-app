<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

trait CleansAuthorisationFixtures
{
    /**
     * Children before parents: the pivot tables carry foreign keys into
     * `permissions` and `roles`.
     */
    protected function deleteAuthorisationFixtures(): void
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
