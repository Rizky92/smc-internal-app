<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class FakturPajakDitarik extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'faktur_pajak_ditarik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'no_rawat',
        'kode_transaksi',
        'tgl_bayar',
        'jam_bayar',
        'status_lanjut',
        'jenis_faktur',
        'keterangan_tambahan',
        'dokumen_pendukung',
        'cap_fasilitas',
        'id_tku_penjual',
        'jenis_id',
        'negara',
        'id_tku',
        'no_rkm_medis',
        'nik_pasien',
        'nama_pasien',
        'alamat_pasien',
        'email_pasien',
        'no_telp_pasien',
        'kode_asuransi',
        'nama_asuransi',
        'alamat_asuransi',
        'telp_asuransi',
        'email_asuransi',
        'npwp_asuransi',
        'kode_perusahaan',
        'nama_perusahaan',
        'alamat_perusahaan',
        'telp_perusahaan',
        'email_perusahaan',
        'npwp_perusahaan',
        'tgl_tarikan',
    ];
}
