<?php

namespace Database\Seeders;

use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\Models\Keuangan\Master\SatuanUkuranPajak;
use App\Models\Keuangan\NotaSelesai;
use App\Models\Keuangan\PiutangDilunaskan;
use App\Settings\NPWPSettings;
use Illuminate\Database\Seeder;

class SmcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NotaSelesai::refreshModel();
        // JurnalMedis::refreshModel();
        // JurnalNonMedis::refreshModel();
        // PiutangDilunaskan::refreshModel();

        SatuanUkuranPajak::insert([
            ['kode_sat' => '', 'kode_satuan_pajak' => 'UM.0033', 'nama_satuan_pajak' => 'Lainnya'],
            ['kode_sat' => '-', 'kode_satuan_pajak' => 'UM.0033', 'nama_satuan_pajak' => 'Lainnya'],
            ['kode_sat' => 'KG', 'kode_satuan_pajak' => 'UM.0003', 'nama_satuan_pajak' => 'Kilogram'],
            ['kode_sat' => 'GRAM', 'kode_satuan_pajak' => 'UM.0004', 'nama_satuan_pajak' => 'Gram'],
            ['kode_sat' => 'L', 'kode_satuan_pajak' => 'UM.0007', 'nama_satuan_pajak' => 'Liter'],
            ['kode_sat' => 'M2', 'kode_satuan_pajak' => 'UM.0012', 'nama_satuan_pajak' => 'Meter Persegi'],
            ['kode_sat' => 'M', 'kode_satuan_pajak' => 'UM.0013', 'nama_satuan_pajak' => 'Meter'],
            ['kode_sat' => 'CM', 'kode_satuan_pajak' => 'UM.0015', 'nama_satuan_pajak' => 'Sentimeter'],
            ['kode_sat' => 'YARD', 'kode_satuan_pajak' => 'UM.0016', 'nama_satuan_pajak' => 'Yard'],
            ['kode_sat' => 'Lus', 'kode_satuan_pajak' => 'UM.0017', 'nama_satuan_pajak' => 'Lusin'],
            ['kode_sat' => 'SET', 'kode_satuan_pajak' => 'UM.0019', 'nama_satuan_pajak' => 'Set'],
            ['kode_sat' => 'Unit', 'kode_satuan_pajak' => 'UM.0018', 'nama_satuan_pajak' => 'Unit'],
            ['kode_sat' => 'LBR', 'kode_satuan_pajak' => 'UM.0020', 'nama_satuan_pajak' => 'Lembar'],
            ['kode_sat' => 'PCS', 'kode_satuan_pajak' => 'UM.0021', 'nama_satuan_pajak' => 'Piece'],
            ['kode_sat' => 'BOX', 'kode_satuan_pajak' => 'UM.0022', 'nama_satuan_pajak' => 'Boks'],
            ['kode_sat' => 'HARI', 'kode_satuan_pajak' => 'UM.0026', 'nama_satuan_pajak' => 'Hari'],
        ]);

        app(NPWPSettings::class)->fill(['npwp_penjual' => '6404051408990003'])->save();
    }
}
