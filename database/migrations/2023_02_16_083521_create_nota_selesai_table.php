<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('nota_selesai', function (Blueprint $table) {
            $table->id();
            $table->string('no_rawat', 20);
            $table->string('status_pasien', 20)->nullable();
            $table->string('bentuk_bayar', 20)->nullable();
            $table->timestamp('tgl_penyelesaian')->nullable();
            $table->string('user_id', 20)->nullable();

            $table->index(['no_rawat', 'user_id']);
        });
    }
};
