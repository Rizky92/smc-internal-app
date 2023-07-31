<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->dropView('laporan_statistik');
        Schema::connection('mysql_smc')->dropView('demografi_pasien');
        Schema::connection('mysql_smc')->dropView('laporan_pasien_ranap');
    }
};
