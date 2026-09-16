<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guards the boundary between the test suite and the databases SIAP reads in
 * development. SIMRS Khanza's schema is not ours and cannot be rebuilt from
 * migrations, so nothing stops a careless test from writing into the copy a
 * developer is actually using. This test is what stops it: the suite refuses to
 * run unless both connections point at schemas reserved for testing.
 */
class TestEnvironmentTest extends TestCase
{
    /**
     * @test
     */
    public function suite_runs_against_isolated_and_reachable_test_schemas(): void
    {
        foreach (['mysql_sik', 'mysql_smc'] as $connection) {
            $database = config("database.connections.{$connection}.database");

            $this->assertStringEndsWith('_test', (string) $database, sprintf(
                'Connection [%s] points at schema [%s]. Tests must only ever run against a schema '
                .'whose name ends in "_test", so that a write can never reach a working database.',
                $connection,
                $database
            ));

            $this->assertSame(
                $database,
                DB::connection($connection)->getDatabaseName(),
                sprintf('Connection [%s] could not be resolved to a live schema.', $connection)
            );
        }
    }
}
