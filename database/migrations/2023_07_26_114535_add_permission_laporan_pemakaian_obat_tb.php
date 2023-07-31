<?php

use App\Models\Aplikasi\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Permission::create(['name' => 'farmasi.laporan-pemakaian-obat-tb.read']);
    }
};
