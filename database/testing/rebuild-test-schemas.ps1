<#
.SYNOPSIS
    Rebuilds the sik_test and smc_test schemas the PHPUnit suite runs against.

.DESCRIPTION
    SIAP reads two databases it does not own the shape of. smc_test can be built
    from migrations; sik_test cannot, because SIMRS Khanza's schema belongs to
    Khanza. So sik_test is a structure-only copy of the local Khanza database,
    plus the small organisational lookup tables that `pegawai`'s foreign keys
    require.

    Nothing here copies patient data. The only rows moved are reference tables:
    departments, job grades, education levels, banks and similar.

    Run this once on a new machine, and again whenever Khanza's structure changes
    underneath you.

.EXAMPLE
    pwsh database/testing/rebuild-test-schemas.ps1
#>

param(
    [string] $MysqlBin  = 'C:\xampp\mysql\bin',
    [string] $PhpBin    = 'C:\xampp\php\php.exe',
    [string] $DbUser    = 'root',
    [string] $SourceSik = 'sik'
)

$ErrorActionPreference = 'Stop'

$mysql = Join-Path $MysqlBin 'mysql.exe'
$dump  = Join-Path $MysqlBin 'mysqldump.exe'
$sqlFile = Join-Path $env:TEMP 'siap_sik_structure.sql'

# Khanza is latin1. The test copies must match, or collation-sensitive
# comparisons behave differently under test than they do in development.
Write-Host '==> Creating schemas' -ForegroundColor Cyan
& $mysql -u $DbUser -e @"
CREATE DATABASE IF NOT EXISTS sik_test CHARACTER SET latin1 COLLATE latin1_swedish_ci;
CREATE DATABASE IF NOT EXISTS smc_test CHARACTER SET latin1 COLLATE latin1_swedish_ci;
DROP DATABASE sik_test;
CREATE DATABASE sik_test CHARACTER SET latin1 COLLATE latin1_swedish_ci;
"@

Write-Host "==> Dumping structure of '$SourceSik' (no data)" -ForegroundColor Cyan
cmd /c "`"$dump`" -u $DbUser --no-data --skip-triggers --skip-lock-tables $SourceSik > `"$sqlFile`""
cmd /c "`"$mysql`" -u $DbUser sik_test < `"$sqlFile`""

# Storage engines are copied from Khanza as-is and deliberately left alone.
#
# Khanza puts its staging and scratch tables on MyISAM on purpose — they are
# working space, not transactional data. Converting them to InnoDB in the test
# copy would make the suite MORE transactional than production, so a write that
# silently fails to roll back in production would appear to roll back cleanly
# under test. That is a false green, and it hides precisely the class of bug the
# suite exists to catch.
#
# The cost is that DatabaseTransactions cannot undo a write to a MyISAM table.
# Any fixture touching one has to clean up after itself; see
# CreatesPetugas::deleteNonTransactionalFixtures(), which does this for `user`.

# Small, organisational tables containing no patient data.
#
# The first eleven are what pegawai's foreign keys point at. The last two are
# configuration that pages read at render time and crash without: `setting` holds
# the institution name and logo, and `closing_kasir` defines the cashier shifts —
# Farmasi\DefectaDepo::dataShiftKerja() is typed `: object` and fatals on an empty
# table rather than degrading.
Write-Host '==> Seeding reference tables' -ForegroundColor Cyan
$reference = @(
    'jnj_jabatan', 'kelompok_jabatan', 'resiko_kerja', 'departemen', 'bidang',
    'stts_wp', 'stts_kerja', 'pendidikan', 'bank', 'emergency_index', 'jabatan',
    'setting', 'closing_kasir'
)
$copy = 'SET FOREIGN_KEY_CHECKS=0;'
foreach ($t in $reference) {
    $copy += " DELETE FROM sik_test.$t; INSERT INTO sik_test.$t SELECT * FROM $SourceSik.$t;"
}
$copy += ' SET FOREIGN_KEY_CHECKS=1;'
& $mysql -u $DbUser -e $copy

Write-Host '==> Migrating smc_test' -ForegroundColor Cyan
$env:SIK_DATABASE = 'sik_test'
$env:SMC_DATABASE = 'smc_test'
$env:APP_ENV      = 'testing'

$target = & $PhpBin artisan tinker --execute="echo config('database.connections.mysql_smc.database');"
if ($target -notmatch 'smc_test') {
    throw "Refusing to migrate: mysql_smc resolved to '$target', not smc_test."
}
& $PhpBin artisan migrate --force

Write-Host ''
Write-Host 'Done. Verify with: php vendor/bin/phpunit --filter=TestEnvironmentTest' -ForegroundColor Green
