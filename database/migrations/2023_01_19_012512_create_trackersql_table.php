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
        Schema::connection('mysql_smc')->create('trackersql', function (Blueprint $table) {
            $table->timestamp('tanggal')->index();
            $table->text('sqle')->index();
            $table->string('usere', 20)->index();
            $table->ipAddress('ip');
            $table->string('connection', 20)->nullable();
        });
    }
};
