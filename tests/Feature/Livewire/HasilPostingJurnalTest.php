<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The printable journal posting sheet.
 *
 * Its letterhead comes from getSIMRSSettingsProperty(), which is honestly
 * declared ?object because it reads the single row of Khanza's `setting` table.
 * The view dereferenced it five times without allowing for that.
 *
 * RouteSweepTest cannot catch this: the reference seed puts the row in, so the
 * page renders. Only removing it shows the difference.
 */
class HasilPostingJurnalTest extends TestCase
{
    private const URI = '/admin/keuangan/cetak-posting-jurnal';

    /** @var list<array<string, mixed>> */
    private $savedSetting = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedSetting = DB::connection('mysql_sik')
            ->table('setting')->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        if ($sik->table('setting')->count() === 0 && $this->savedSetting !== []) {
            $sik->table('setting')->insert($this->savedSetting);
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function prints_the_letterhead_when_khanza_has_settings(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.posting-jurnal.read'], '99999901');

        $this->withoutExceptionHandling();

        $this->actingAs($petugas)->get(self::URI)->assertOk();
    }

    /**
     * @test
     *
     * An unconfigured or freshly restored Khanza has no setting row. The sheet
     * should print without a letterhead rather than not print at all.
     */
    public function still_prints_when_khanza_has_no_settings(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.posting-jurnal.read'], '99999901');

        DB::connection('mysql_sik')->table('setting')->delete();

        $this->withoutExceptionHandling();

        $this->actingAs($petugas)->get(self::URI)->assertOk();
    }
}
