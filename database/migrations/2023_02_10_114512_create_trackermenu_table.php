<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackermenuTable extends Migration
{
    protected $connection = 'mysql_smc';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('trackermenu', function (Blueprint $table) {
            $table->timestamp('waktu')->useCurrent();
            $table->string('breadcrumbs', 100)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->string('user_id', 20)->index();
            $table->ipAddress('ip_address');
        });
    }
}
