<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackermenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('trackermenu', function (Blueprint $table) {
            $table->timestamp('waktu')->useCurrent();
            $table->string('breadcrumbs')->nullable();
            $table->string('route')->nullable();
            $table->string('user_id', 20)->index();
            $table->ipAddress('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
