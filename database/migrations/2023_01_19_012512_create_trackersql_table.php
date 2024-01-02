<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('trackersql', function (Blueprint $table): void {
            $table->timestamp('tanggal', $precision = 6)->index();
            $table->text('sqle')->index();
            $table->string('usere', 20)->index();
            $table->ipAddress('ip');
            $table->string('connection', 20)->nullable();
        });
    }
};
