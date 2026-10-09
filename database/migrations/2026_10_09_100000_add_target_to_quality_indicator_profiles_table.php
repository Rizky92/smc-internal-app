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
     * sebelum dan sesudah migrasi. `target_operator` sengaja tidak diisi (tidak ada default ≥); arah
     * target dipastikan bersama Komite Mutu saat rollout.
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
                $value = StandardParser::parse($profile->standard);

                if ($value !== null) {
                    $db->table('quality_indicator_profiles')->where('id', $profile->id)->update(['target_value' => $value]);
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
