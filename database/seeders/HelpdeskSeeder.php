<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HelpdeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ----------------------------------------------------------------
        // Ticket Categories
        // ----------------------------------------------------------------
        $categories = [
            ['code' => 'SW',    'name' => 'Software / Aplikasi',        'color' => '#185FA5', 'sort_order' => 1, 'description' => 'Masalah aplikasi SIMRS, billing, email, dan software lainnya'],
            ['code' => 'HW',    'name' => 'Hardware / Komputer',         'color' => '#854F0B', 'sort_order' => 2, 'description' => 'Kerusakan komputer, printer, monitor, dan perangkat keras lainnya'],
            ['code' => 'NET',   'name' => 'Jaringan / Network',          'color' => '#3B6D11', 'sort_order' => 3, 'description' => 'Masalah koneksi internet, WiFi, LAN, dan infrastruktur jaringan'],
            ['code' => 'INST',  'name' => 'Instalasi',                   'color' => '#3C3489', 'sort_order' => 4, 'description' => 'Instalasi komputer baru, setup software, join domain'],
            ['code' => 'SEC',   'name' => 'Keamanan / Security',         'color' => '#A32D2D', 'sort_order' => 5, 'description' => 'CCTV, akses kontrol, keamanan data, dan insiden keamanan'],
            ['code' => 'OTHER', 'name' => 'Lainnya',                     'color' => '#5F5E5A', 'sort_order' => 6, 'description' => 'Permintaan atau masalah yang tidak termasuk kategori di atas'],
        ];

        DB::table('ticket_categories')->insert(
            array_map(fn ($c) => array_merge($c, [
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]), $categories)
        );

        // ----------------------------------------------------------------
        // SLA Policies — sesuai standar IT support layanan kesehatan
        // ----------------------------------------------------------------
        DB::table('sla_policies')->insert([
            // Critical: sistem inti RS mati (SIMRS, server, network backbone)
            ['priority' => 'critical', 'response_hours' => 1,  'resolution_hours' => 4,  'created_at' => $now, 'updated_at' => $now],
            // High: berdampak ke banyak user / unit penting
            ['priority' => 'high',     'response_hours' => 2,  'resolution_hours' => 8,  'created_at' => $now, 'updated_at' => $now],
            // Medium: gangguan tapi ada workaround
            ['priority' => 'medium',   'response_hours' => 4,  'resolution_hours' => 24, 'created_at' => $now, 'updated_at' => $now],
            // Low: tidak mengganggu operasional
            ['priority' => 'low',      'response_hours' => 8,  'resolution_hours' => 72, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
