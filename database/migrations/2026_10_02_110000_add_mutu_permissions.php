<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionSeeder mengosongkan semua izin, role, dan penugasannya sebelum mengisi ulang, jadi tidak
 * boleh dijalankan ulang di production. Izin Indikator Mutu ditambahkan lewat migrasi aditif ini: hanya
 * menambah yang belum ada, dan down() hanya menghapus izin ini.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'mutu.kategori-indikator.create',
        'mutu.kategori-indikator.read',
        'mutu.kategori-indikator.update',
        'mutu.kategori-indikator.delete',
        'mutu.tipe-input-indikator.create',
        'mutu.tipe-input-indikator.read',
        'mutu.tipe-input-indikator.update',
        'mutu.tipe-input-indikator.delete',
        'mutu.indikator-mutu.create',
        'mutu.indikator-mutu.read',
        'mutu.indikator-mutu.update',
        'mutu.indikator-mutu.delete',
        'mutu.validasi-data.read',
        'mutu.validasi-data.approve',
        'mutu.validasi-data.reject',
    ];

    public function up(): void
    {
        $table = DB::connection('mysql_smc')->table(config('permission.table_names.permissions'));
        $ada = $table->clone()->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->pluck('name');

        $table->insert(collect(self::PERMISSIONS)
            ->diff($ada)
            ->map(fn (string $name): array => ['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()])
            ->values()
            ->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::connection('mysql_smc')
            ->table(config('permission.table_names.permissions'))
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
