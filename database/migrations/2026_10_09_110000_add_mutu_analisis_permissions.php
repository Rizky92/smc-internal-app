<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionSeeder mengosongkan semua izin, role, dan penugasannya sebelum mengisi ulang, jadi tidak
 * boleh dijalankan ulang di production. Izin analisis ditambahkan lewat migrasi aditif ini: hanya
 * menambah yang belum ada, dan down() hanya menghapus izin ini.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'mutu.analisis.read',
        'mutu.analisis.create',
        'mutu.analisis.update',
        'mutu.review-analisis.read',
        'mutu.review-analisis.approve',
        'mutu.review-analisis.request-revision',
        'mutu.review-analisis.verify',
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
