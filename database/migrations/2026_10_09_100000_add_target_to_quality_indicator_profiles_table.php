<?php

use App\Support\Mutu\StandardParser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aditif: hanya menambah kolom nullable. `standard` tidak diubah agar `mutu:audit-profil` tetap valid
     * sebelum dan sesudah migrasi. `target_operator` hanya diisi bila teks standar menulis arah secara
     * eksplisit (≥, >=, ≤, <=, "minimal", "maksimal"); tidak ada default ≥. Sisanya dipastikan bersama
     * Komite Mutu saat rollout dan muncul di audit.
     */
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_profiles', function (Blueprint $table): void {
            $table->enum('target_operator', ['gte', 'lte'])->nullable()->after('standard');
            $table->decimal('target_value', 8, 2)->nullable()->after('target_operator');
        });

        $db = DB::connection('mysql_smc');

        $db->table('quality_indicator_profiles')
            ->select(['id', 'standard'])
            ->orderBy('id')
            ->get()
            ->each(function (object $profile) use ($db): void {
                $target = array_filter([
                    'target_value'    => StandardParser::parse($profile->standard),
                    'target_operator' => StandardParser::parseOperator($profile->standard),
                ], fn ($value): bool => $value !== null);

                if ($target) {
                    $db->table('quality_indicator_profiles')->where('id', $profile->id)->update($target);
                }
            });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_profiles', function (Blueprint $table): void {
            $table->dropColumn(['target_operator', 'target_value']);
        });
    }
};
