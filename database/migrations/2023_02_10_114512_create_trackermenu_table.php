<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var ?string
     */
    protected $connection = 'mysql_smc';
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('trackermenu', function (Blueprint $table): void {
            $table->timestamp('waktu', $precision = 6)->useCurrent();
            $table->string('breadcrumbs', 100)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->string('user_id', 20)->index();
            $table->ipAddress('ip_address');
        });
    }
};
