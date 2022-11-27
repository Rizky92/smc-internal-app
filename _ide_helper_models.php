<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Asuransi
 *
 * @property string $kd_pj
 * @property string $png_jawab
 * @property string $nama_perusahaan
 * @property string $alamat_asuransi
 * @property string $no_telp
 * @property string $attn
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pasien[] $pasien
 * @property-read int|null $pasien_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Registrasi[] $registrasi
 * @property-read int|null $registrasi_count
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereAlamatAsuransi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereAttn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereNamaPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi wherePngJawab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asuransi whereStatus($value)
 */
	class Asuransi extends \Eloquent {}
}

namespace App{
/**
 * App\Bangsal
 *
 * @property string $kd_bangsal
 * @property string|null $nm_bangsal
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal whereKdBangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal whereNmBangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bangsal whereStatus($value)
 */
	class Bangsal extends \Eloquent {}
}

namespace App{
/**
 * App\DataBarang
 *
 * @property string $kode_brng
 * @property string|null $nama_brng
 * @property string $kode_satbesar
 * @property string|null $kode_sat
 * @property string|null $letak_barang
 * @property float $dasar
 * @property float|null $h_beli
 * @property float|null $ralan
 * @property float|null $kelas1
 * @property float|null $kelas2
 * @property float|null $kelas3
 * @property float|null $utama
 * @property float|null $vip
 * @property float|null $vvip
 * @property float|null $beliluar
 * @property float|null $jualbebas
 * @property float|null $karyawan
 * @property float|null $stokminimal
 * @property string|null $kdjns
 * @property float $isi
 * @property float $kapasitas
 * @property string|null $expire
 * @property string $status
 * @property string|null $kode_industri
 * @property string|null $kode_kategori
 * @property string|null $kode_golongan
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang daruratStok()
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang janganTampilkanStokMinimalNol()
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang query()
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereBeliluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereDasar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereHBeli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereIsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereJualbebas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKapasitas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKaryawan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKdjns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKelas1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKelas2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKelas3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeBrng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeGolongan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeIndustri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeSat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereKodeSatbesar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereLetakBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereNamaBrng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereStokminimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereUtama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DataBarang whereVvip($value)
 */
	class DataBarang extends \Eloquent {}
}

namespace App{
/**
 * App\Diagnosa
 *
 * @property string $no_rawat
 * @property string $kd_penyakit
 * @property string $status
 * @property int $prioritas
 * @property string|null $status_penyakit
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa whereKdPenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa wherePrioritas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnosa whereStatusPenyakit($value)
 */
	class Diagnosa extends \Eloquent {}
}

namespace App{
/**
 * App\Dokter
 *
 * @property string $kd_dokter
 * @property string|null $nm_dokter
 * @property string|null $jk
 * @property string|null $tmp_lahir
 * @property string|null $tgl_lahir
 * @property string|null $gol_drh
 * @property string|null $agama
 * @property string|null $almt_tgl
 * @property string|null $no_telp
 * @property string|null $stts_nikah
 * @property string|null $kd_sps
 * @property string|null $alumni
 * @property string|null $no_ijn_praktek
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereAlmtTgl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereAlumni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereGolDrh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereJk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereKdSps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereNmDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereNoIjnPraktek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereSttsNikah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereTglLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dokter whereTmpLahir($value)
 */
	class Dokter extends \Eloquent {}
}

namespace App{
/**
 * App\JenisPerawatanRalan
 *
 * @property string $kd_jenis_prw
 * @property string|null $nm_perawatan
 * @property string|null $kd_kategori
 * @property float|null $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $total_byrdr
 * @property float|null $total_byrpr
 * @property float $total_byrdrpr
 * @property string $kd_pj
 * @property string $kd_poli
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan query()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereKdKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereKdPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereNmPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereTotalByrdr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereTotalByrdrpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRalan whereTotalByrpr($value)
 */
	class JenisPerawatanRalan extends \Eloquent {}
}

namespace App{
/**
 * App\JenisPerawatanRanap
 *
 * @property string $kd_jenis_prw
 * @property string|null $nm_perawatan
 * @property string $kd_kategori
 * @property float|null $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $total_byrdr
 * @property float|null $total_byrpr
 * @property float $total_byrdrpr
 * @property string $kd_pj
 * @property string $kd_bangsal
 * @property string $status
 * @property string $kelas
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap query()
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKdBangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKdKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereNmPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereTotalByrdr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereTotalByrdrpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JenisPerawatanRanap whereTotalByrpr($value)
 */
	class JenisPerawatanRanap extends \Eloquent {}
}

namespace App{
/**
 * App\Kabupaten
 *
 * @property int $kd_kab
 * @property string $nm_kab
 * @method static \Illuminate\Database\Eloquent\Builder|Kabupaten newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kabupaten newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kabupaten query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kabupaten whereKdKab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kabupaten whereNmKab($value)
 */
	class Kabupaten extends \Eloquent {}
}

namespace App{
/**
 * App\Kamar
 *
 * @property string $kd_kamar
 * @property string|null $kd_bangsal
 * @property float|null $trf_kamar
 * @property string|null $status
 * @property string|null $kelas
 * @property string|null $statusdata
 * @property-read \App\Bangsal|null $bangsal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Registrasi[] $rawatInap
 * @property-read int|null $rawat_inap_count
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereKdBangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereKdKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereStatusdata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kamar whereTrfKamar($value)
 */
	class Kamar extends \Eloquent {}
}

namespace App{
/**
 * App\KategoriBarang
 *
 * @method static \Illuminate\Database\Eloquent\Builder|KategoriBarang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KategoriBarang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KategoriBarang query()
 */
	class KategoriBarang extends \Eloquent {}
}

namespace App{
/**
 * App\Kecamatan
 *
 * @property int $kd_kec
 * @property string $nm_kec
 * @method static \Illuminate\Database\Eloquent\Builder|Kecamatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kecamatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kecamatan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kecamatan whereKdKec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kecamatan whereNmKec($value)
 */
	class Kecamatan extends \Eloquent {}
}

namespace App{
/**
 * App\Kelurahan
 *
 * @property int $kd_kel
 * @property string $nm_kel
 * @method static \Illuminate\Database\Eloquent\Builder|Kelurahan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kelurahan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kelurahan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kelurahan whereKdKel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kelurahan whereNmKel($value)
 */
	class Kelurahan extends \Eloquent {}
}

namespace App{
/**
 * App\Laporan
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Laporan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Laporan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Laporan query()
 */
	class Laporan extends \Eloquent {}
}

namespace App{
/**
 * App\Pasien
 *
 * @property string $no_rkm_medis
 * @property string|null $nm_pasien
 * @property string|null $no_ktp
 * @property string|null $jk
 * @property string|null $tmp_lahir
 * @property string|null $tgl_lahir
 * @property string $nm_ibu
 * @property string|null $alamat
 * @property string|null $gol_darah
 * @property string|null $pekerjaan
 * @property string|null $stts_nikah
 * @property string|null $agama
 * @property string|null $tgl_daftar
 * @property string|null $no_tlp
 * @property string $umur
 * @property string $pnd
 * @property string|null $keluarga
 * @property string $namakeluarga
 * @property string $kd_pj
 * @property string|null $no_peserta
 * @property int $kd_kel
 * @property int $kd_kec
 * @property int $kd_kab
 * @property string $pekerjaanpj
 * @property string $alamatpj
 * @property string $kelurahanpj
 * @property string $kecamatanpj
 * @property string $kabupatenpj
 * @property string $perusahaan_pasien
 * @property int $suku_bangsa
 * @property int $bahasa_pasien
 * @property int $cacat_fisik
 * @property string $email
 * @property string $nip
 * @property int $kd_prop
 * @property string $propinsipj
 * @property-read \App\Kabupaten $kabupaten
 * @property-read \App\Kecamatan $kecamatan
 * @property-read \App\Kelurahan $kelurahan
 * @property-read \App\Provinsi $provinsi
 * @property-read \App\Suku $suku
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereAlamatpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereBahasaPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereCacatFisik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereGolDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereJk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKabupatenpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKdKab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKdKec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKdKel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKdProp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKecamatanpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereKelurahanpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNamakeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNmIbu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNmPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNoKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNoPeserta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNoRkmMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereNoTlp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien wherePekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien wherePekerjaanpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien wherePerusahaanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien wherePnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien wherePropinsipj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereSttsNikah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereSukuBangsa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereTglDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereTglLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereTmpLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pasien whereUmur($value)
 */
	class Pasien extends \Eloquent {}
}

namespace App{
/**
 * App\Penjamin
 *
 * @property string $kd_pj
 * @property string $png_jawab
 * @property string $nama_perusahaan
 * @property string $alamat_asuransi
 * @property string $no_telp
 * @property string $attn
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereAlamatAsuransi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereAttn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereNamaPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin wherePngJawab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penjamin whereStatus($value)
 */
	class Penjamin extends \Eloquent {}
}

namespace App{
/**
 * App\Penyakit
 *
 * @property string $kd_penyakit
 * @property string|null $nm_penyakit
 * @property string|null $ciri_ciri
 * @property string|null $keterangan
 * @property string|null $kd_ktg
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereCiriCiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereKdKtg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereKdPenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereNmPenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyakit whereStatus($value)
 */
	class Penyakit extends \Eloquent {}
}

namespace App{
/**
 * App\Petugas
 *
 * @property string $nip
 * @property string|null $nama
 * @property string|null $jk
 * @property string|null $tmp_lahir
 * @property string|null $tgl_lahir
 * @property string|null $gol_darah
 * @property string|null $agama
 * @property string|null $stts_nikah
 * @property string|null $alamat
 * @property string|null $kd_jbtn
 * @property string|null $no_telp
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereGolDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereJk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereKdJbtn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereSttsNikah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereTglLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas whereTmpLahir($value)
 */
	class Petugas extends \Eloquent {}
}

namespace App{
/**
 * App\Poliklinik
 *
 * @property string $kd_poli
 * @property string|null $nm_poli
 * @property \Illuminate\Database\Eloquent\Collection|\App\Registrasi[] $registrasi
 * @property float $registrasilama
 * @property string $status
 * @property-read int|null $registrasi_count
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik query()
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik whereKdPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik whereNmPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik whereRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik whereRegistrasilama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poliklinik whereStatus($value)
 */
	class Poliklinik extends \Eloquent {}
}

namespace App{
/**
 * App\Provinsi
 *
 * @property int $kd_prop
 * @property string $nm_prop
 * @method static \Illuminate\Database\Eloquent\Builder|Provinsi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Provinsi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Provinsi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Provinsi whereKdProp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provinsi whereNmProp($value)
 */
	class Provinsi extends \Eloquent {}
}

namespace App{
/**
 * App\RawatInap
 *
 * @property string $no_rawat
 * @property string $kd_kamar
 * @property float|null $trf_kamar
 * @property string|null $diagnosa_awal
 * @property string|null $diagnosa_akhir
 * @property \Illuminate\Support\Carbon $tgl_masuk
 * @property \Illuminate\Support\Carbon $jam_masuk
 * @property \Illuminate\Support\Carbon|null $tgl_keluar
 * @property \Illuminate\Support\Carbon|null $jam_keluar
 * @property float|null $lama
 * @property float|null $ttl_biaya
 * @property string $stts_pulang
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap query()
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereDiagnosaAkhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereDiagnosaAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereKdKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereLama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereSttsPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereTglKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereTglMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereTrfKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawatInap whereTtlBiaya($value)
 */
	class RawatInap extends \Eloquent {}
}

namespace App{
/**
 * App\Registrasi
 *
 * @property string|null $no_reg
 * @property string $no_rawat
 * @property \Illuminate\Support\Carbon|null $tgl_registrasi
 * @property \Illuminate\Support\Carbon|null $jam_reg
 * @property string|null $kd_dokter
 * @property string|null $no_rkm_medis
 * @property string|null $kd_poli
 * @property string|null $p_jawab
 * @property string|null $almt_pj
 * @property string|null $hubunganpj
 * @property float|null $biaya_reg
 * @property string|null $stts
 * @property string $stts_daftar
 * @property string $status_lanjut
 * @property string $kd_pj
 * @property int|null $umurdaftar
 * @property string|null $sttsumur
 * @property string $status_bayar
 * @property string $status_poli
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Penyakit[] $diagnosa
 * @property-read int|null $diagnosa_count
 * @property-read \App\Dokter|null $dokter
 * @property-read \App\Pasien|null $pasien
 * @property-read \App\Penjamin $penjamin
 * @property-read \App\Poliklinik|null $poliklinik
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Kamar[] $rawatInap
 * @property-read int|null $rawat_inap_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRalan[] $tindakanRalanDokter
 * @property-read int|null $tindakan_ralan_dokter_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRalan[] $tindakanRalanDokterPerawat
 * @property-read int|null $tindakan_ralan_dokter_perawat_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRalan[] $tindakanRalanPerawat
 * @property-read int|null $tindakan_ralan_perawat_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRanap[] $tindakanRanapDokter
 * @property-read int|null $tindakan_ranap_dokter_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRanap[] $tindakanRanapDokterPerawat
 * @property-read int|null $tindakan_ranap_dokter_perawat_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\JenisPerawatanRanap[] $tindakanRanapPerawat
 * @property-read int|null $tindakan_ranap_perawat_count
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi laporanKunjunganRalan()
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi laporanStatistik($periodeAwal, $periodeAkhir)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereAlmtPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereBiayaReg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereHubunganpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereJamReg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereKdPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereNoReg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereNoRkmMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi wherePJawab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereStatusBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereStatusLanjut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereStatusPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereStts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereSttsDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereSttsumur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereTglRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Registrasi whereUmurdaftar($value)
 */
	class Registrasi extends \Eloquent {}
}

namespace App{
/**
 * App\Resep
 *
 * @property string $no_resep
 * @property string|null $tgl_perawatan
 * @property string $jam
 * @property string $no_rawat
 * @property string $kd_dokter
 * @property string|null $tgl_peresepan
 * @property string|null $jam_peresepan
 * @property string|null $status
 * @property string $tgl_penyerahan
 * @property string $jam_penyerahan
 * @property-read \App\Dokter $dokterPeresep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DataBarang[] $obat
 * @property-read int|null $obat_count
 * @method static \Illuminate\Database\Eloquent\Builder|Resep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resep penggunaanObatPerDokter($dateMin, $dateMax)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep query()
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereJam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereJamPenyerahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereJamPeresepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereNoResep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereTglPenyerahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereTglPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resep whereTglPeresepan($value)
 */
	class Resep extends \Eloquent {}
}

namespace App{
/**
 * App\ResepDokter
 *
 * @property string|null $no_resep
 * @property string|null $kode_brng
 * @property float|null $jml
 * @property string|null $aturan_pakai
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter whereAturanPakai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter whereJml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter whereKodeBrng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResepDokter whereNoResep($value)
 */
	class ResepDokter extends \Eloquent {}
}

namespace App{
/**
 * App\RujukanKeluar
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanKeluar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanKeluar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanKeluar query()
 */
	class RujukanKeluar extends \Eloquent {}
}

namespace App{
/**
 * App\RujukanMasuk
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanMasuk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanMasuk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RujukanMasuk query()
 */
	class RujukanMasuk extends \Eloquent {}
}

namespace App{
/**
 * App\Satuan
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan query()
 */
	class Satuan extends \Eloquent {}
}

namespace App{
/**
 * App\Suku
 *
 * @property int $id
 * @property string|null $nama_suku_bangsa
 * @method static \Illuminate\Database\Eloquent\Builder|Suku newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Suku newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Suku query()
 * @method static \Illuminate\Database\Eloquent\Builder|Suku whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Suku whereNamaSukuBangsa($value)
 */
	class Suku extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRalan
 *
 * @property string $kd_jenis_prw
 * @property string|null $nm_perawatan
 * @property string|null $kd_kategori
 * @property float|null $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $total_byrdr
 * @property float|null $total_byrpr
 * @property float $total_byrdrpr
 * @property string $kd_pj
 * @property string $kd_poli
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereKdKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereKdPj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereKdPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereNmPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereTotalByrdr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereTotalByrdrpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalan whereTotalByrpr($value)
 */
	class TindakanRalan extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRalanDokter
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $kd_dokter
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float $material
 * @property float $bhp
 * @property float $tarif_tindakandr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property string|null $stts_bayar
 * @property-read \App\Dokter $dokter
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereSttsBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokter whereTglPerawatan($value)
 */
	class TindakanRalanDokter extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRalanDokterPerawat
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $kd_dokter
 * @property string $nip
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float|null $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property string|null $stts_bayar
 * @property-read \App\Dokter $dokter
 * @property-read \App\Petugas $perawat
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereSttsBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanDokterPerawat whereTglPerawatan($value)
 */
	class TindakanRalanDokterPerawat extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRalanPerawat
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $kd_dokter
 * @property string $nip
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float|null $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property string|null $stts_bayar
 * @property-read \App\Petugas $perawat
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereSttsBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRalanPerawat whereTglPerawatan($value)
 */
	class TindakanRalanPerawat extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRanapDokter
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $kd_dokter
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float $material
 * @property float $bhp
 * @property float $tarif_tindakandr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property-read \App\Dokter $dokter
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokter whereTglPerawatan($value)
 */
	class TindakanRanapDokter extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRanapDokterPerawat
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $kd_dokter
 * @property string $nip
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float $material
 * @property float $bhp
 * @property float|null $tarif_tindakandr
 * @property float|null $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property-read \App\Dokter $dokter
 * @property-read \App\Petugas $perawat
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereKdDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereTarifTindakandr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapDokterPerawat whereTglPerawatan($value)
 */
	class TindakanRanapDokterPerawat extends \Eloquent {}
}

namespace App{
/**
 * App\TindakanRanapPerawat
 *
 * @property string $no_rawat
 * @property string $kd_jenis_prw
 * @property string $nip
 * @property string $tgl_perawatan
 * @property string $jam_rawat
 * @property float $material
 * @property float $bhp
 * @property float $tarif_tindakanpr
 * @property float|null $kso
 * @property float|null $menejemen
 * @property float|null $biaya_rawat
 * @property-read \App\Petugas $perawat
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat query()
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereBiayaRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereJamRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereKdJenisPrw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereNoRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereTarifTindakanpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TindakanRanapPerawat whereTglPerawatan($value)
 */
	class TindakanRanapPerawat extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property string $id_user
 * @property string $password
 * @property string $penyakit
 * @property string $obat_penyakit
 * @property string $dokter
 * @property string $jadwal_praktek
 * @property string $petugas
 * @property string $pasien
 * @property string $registrasi
 * @property string $tindakan_ralan
 * @property string $kamar_inap
 * @property string $tindakan_ranap
 * @property string $operasi
 * @property string $rujukan_keluar
 * @property string $rujukan_masuk
 * @property string $beri_obat
 * @property string $resep_pulang
 * @property string $pasien_meninggal
 * @property string $diet_pasien
 * @property string $kelahiran_bayi
 * @property string $periksa_lab
 * @property string $periksa_radiologi
 * @property string $kasir_ralan
 * @property string $deposit_pasien
 * @property string $piutang_pasien
 * @property string $peminjaman_berkas
 * @property string $barcode
 * @property string $presensi_harian
 * @property string $presensi_bulanan
 * @property string $pegawai_admin
 * @property string $pegawai_user
 * @property string $suplier
 * @property string $satuan_barang
 * @property string $konversi_satuan
 * @property string $jenis_barang
 * @property string $obat
 * @property string $stok_opname_obat
 * @property string $stok_obat_pasien
 * @property string $pengadaan_obat
 * @property string $pemesanan_obat
 * @property string $penjualan_obat
 * @property string $piutang_obat
 * @property string $retur_ke_suplier
 * @property string $retur_dari_pembeli
 * @property string $retur_obat_ranap
 * @property string $retur_piutang_pasien
 * @property string $keuntungan_penjualan
 * @property string $keuntungan_beri_obat
 * @property string $sirkulasi_obat
 * @property string $ipsrs_barang
 * @property string $ipsrs_pengadaan_barang
 * @property string $ipsrs_stok_keluar
 * @property string $ipsrs_rekap_pengadaan
 * @property string $ipsrs_rekap_stok_keluar
 * @property string $ipsrs_pengeluaran_harian
 * @property string $inventaris_jenis
 * @property string $inventaris_kategori
 * @property string $inventaris_merk
 * @property string $inventaris_ruang
 * @property string $inventaris_produsen
 * @property string $inventaris_koleksi
 * @property string $inventaris_inventaris
 * @property string $inventaris_sirkulasi
 * @property string $parkir_jenis
 * @property string $parkir_in
 * @property string $parkir_out
 * @property string $parkir_rekap_harian
 * @property string $parkir_rekap_bulanan
 * @property string $informasi_kamar
 * @property string $harian_tindakan_poli
 * @property string $obat_per_poli
 * @property string $obat_per_kamar
 * @property string $obat_per_dokter_ralan
 * @property string $obat_per_dokter_ranap
 * @property string $harian_dokter
 * @property string $bulanan_dokter
 * @property string $harian_paramedis
 * @property string $bulanan_paramedis
 * @property string $pembayaran_ralan
 * @property string $pembayaran_ranap
 * @property string $rekap_pembayaran_ralan
 * @property string $rekap_pembayaran_ranap
 * @property string $tagihan_masuk
 * @property string $tambahan_biaya
 * @property string $potongan_biaya
 * @property string $resep_obat
 * @property string $resume_pasien
 * @property string $penyakit_ralan
 * @property string $penyakit_ranap
 * @property string $kamar
 * @property string $tarif_ralan
 * @property string $tarif_ranap
 * @property string $tarif_lab
 * @property string $tarif_radiologi
 * @property string $tarif_operasi
 * @property string $akun_rekening
 * @property string $rekening_tahun
 * @property string $posting_jurnal
 * @property string $buku_besar
 * @property string $cashflow
 * @property string $keuangan
 * @property string $pengeluaran
 * @property string $setup_pjlab
 * @property string $setup_otolokasi
 * @property string $setup_jam_kamin
 * @property string $setup_embalase
 * @property string $tracer_login
 * @property string $display
 * @property string $set_harga_obat
 * @property string $set_penggunaan_tarif
 * @property string $set_oto_ralan
 * @property string $biaya_harian
 * @property string $biaya_masuk_sekali
 * @property string $set_no_rm
 * @property string $billing_ralan
 * @property string $billing_ranap
 * @property string $jm_ranap_dokter
 * @property string $igd
 * @property string $barcoderalan
 * @property string $barcoderanap
 * @property string $set_harga_obat_ralan
 * @property string $set_harga_obat_ranap
 * @property string $penyakit_pd3i
 * @property string $surveilans_pd3i
 * @property string $surveilans_ralan
 * @property string $diagnosa_pasien
 * @property string $surveilans_ranap
 * @property string $pny_takmenular_ranap
 * @property string $pny_takmenular_ralan
 * @property string $kunjungan_ralan
 * @property string $rl32
 * @property string $rl33
 * @property string $rl37
 * @property string $rl38
 * @property string $harian_tindakan_dokter
 * @property string $sms
 * @property string $sidikjari
 * @property string $jam_masuk
 * @property string $jadwal_pegawai
 * @property string $parkir_barcode
 * @property string $set_nota
 * @property string $dpjp_ranap
 * @property string $mutasi_barang
 * @property string|null $rl34
 * @property string $rl36
 * @property string|null $fee_visit_dokter
 * @property string|null $fee_bacaan_ekg
 * @property string|null $fee_rujukan_rontgen
 * @property string|null $fee_rujukan_ranap
 * @property string|null $fee_ralan
 * @property string|null $akun_bayar
 * @property string|null $bayar_pemesanan_obat
 * @property string|null $obat_per_dokter_peresep
 * @property string|null $ipsrs_jenis_barang
 * @property string|null $pemasukan_lain
 * @property string|null $pengaturan_rekening
 * @property string|null $closing_kasir
 * @property string|null $keterlambatan_presensi
 * @property string|null $set_harga_kamar
 * @property string|null $rekap_per_shift
 * @property string|null $bpjs_cek_nik
 * @property string|null $bpjs_cek_kartu
 * @property string|null $bpjs_cek_riwayat
 * @property string|null $obat_per_cara_bayar
 * @property string|null $kunjungan_ranap
 * @property string|null $bayar_piutang
 * @property string|null $payment_point
 * @property string|null $bpjs_cek_nomor_rujukan
 * @property string|null $icd9
 * @property string|null $darurat_stok
 * @property string|null $retensi_rm
 * @property string|null $temporary_presensi
 * @property string|null $jurnal_harian
 * @property string|null $sirkulasi_obat2
 * @property string|null $edit_registrasi
 * @property string|null $bpjs_referensi_diagnosa
 * @property string|null $bpjs_referensi_poli
 * @property string|null $industrifarmasi
 * @property string|null $harian_js
 * @property string|null $bulanan_js
 * @property string|null $harian_paket_bhp
 * @property string|null $bulanan_paket_bhp
 * @property string|null $piutang_pasien2
 * @property string|null $bpjs_referensi_faskes
 * @property string|null $bpjs_sep
 * @property string|null $pengambilan_utd
 * @property string|null $tarif_utd
 * @property string|null $pengambilan_utd2
 * @property string|null $utd_medis_rusak
 * @property string|null $pengambilan_penunjang_utd
 * @property string|null $pengambilan_penunjang_utd2
 * @property string|null $utd_penunjang_rusak
 * @property string|null $suplier_penunjang
 * @property string|null $utd_donor
 * @property string|null $bpjs_monitoring_klaim
 * @property string|null $utd_cekal_darah
 * @property string|null $utd_komponen_darah
 * @property string|null $utd_stok_darah
 * @property string|null $utd_pemisahan_darah
 * @property string|null $harian_kamar
 * @property string|null $rincian_piutang_pasien
 * @property string|null $keuntungan_beri_obat_nonpiutang
 * @property string|null $reklasifikasi_ralan
 * @property string|null $reklasifikasi_ranap
 * @property string|null $utd_penyerahan_darah
 * @property string|null $hutang_obat
 * @property string|null $riwayat_obat_alkes_bhp
 * @property string|null $sensus_harian_poli
 * @property string|null $rl4a
 * @property string|null $aplicare_referensi_kamar
 * @property string|null $aplicare_ketersediaan_kamar
 * @property string|null $inacbg_klaim_baru_otomatis
 * @property string|null $inacbg_klaim_baru_manual
 * @property string|null $inacbg_coder_nik
 * @property string|null $mutasi_berkas
 * @property string|null $akun_piutang
 * @property string|null $harian_kso
 * @property string|null $bulanan_kso
 * @property string|null $harian_menejemen
 * @property string|null $bulanan_menejemen
 * @property string|null $inhealth_cek_eligibilitas
 * @property string|null $inhealth_referensi_jenpel_ruang_rawat
 * @property string|null $inhealth_referensi_poli
 * @property string|null $inhealth_referensi_faskes
 * @property string|null $inhealth_sjp
 * @property string|null $piutang_ralan
 * @property string|null $piutang_ranap
 * @property string|null $detail_piutang_penjab
 * @property string|null $lama_pelayanan_ralan
 * @property string|null $catatan_pasien
 * @property string|null $rl4b
 * @property string|null $rl4asebab
 * @property string|null $rl4bsebab
 * @property string|null $data_HAIs
 * @property string|null $harian_HAIs
 * @property string|null $bulanan_HAIs
 * @property string|null $hitung_bor
 * @property string|null $perusahaan_pasien
 * @property string|null $resep_dokter
 * @property string|null $lama_pelayanan_apotek
 * @property string|null $hitung_alos
 * @property string|null $detail_tindakan
 * @property string|null $rujukan_poli_internal
 * @property string|null $rekap_poli_anak
 * @property string|null $grafik_kunjungan_poli
 * @property string|null $grafik_kunjungan_perdokter
 * @property string|null $grafik_kunjungan_perpekerjaan
 * @property string|null $grafik_kunjungan_perpendidikan
 * @property string|null $grafik_kunjungan_pertahun
 * @property string|null $berkas_digital_perawatan
 * @property string|null $penyakit_menular_ranap
 * @property string|null $penyakit_menular_ralan
 * @property string|null $grafik_kunjungan_perbulan
 * @property string|null $grafik_kunjungan_pertanggal
 * @property string|null $grafik_kunjungan_demografi
 * @property string|null $grafik_kunjungan_statusdaftartahun
 * @property string|null $grafik_kunjungan_statusdaftartahun2
 * @property string|null $grafik_kunjungan_statusdaftarbulan
 * @property string|null $grafik_kunjungan_statusdaftarbulan2
 * @property string|null $grafik_kunjungan_statusdaftartanggal
 * @property string|null $grafik_kunjungan_statusdaftartanggal2
 * @property string|null $grafik_kunjungan_statusbataltahun
 * @property string|null $grafik_kunjungan_statusbatalbulan
 * @property string|null $pcare_cek_penyakit
 * @property string|null $grafik_kunjungan_statusbataltanggal
 * @property string|null $kategori_barang
 * @property string|null $golongan_barang
 * @property string|null $pemberian_obat_pertanggal
 * @property string|null $penjualan_obat_pertanggal
 * @property string|null $pcare_cek_kesadaran
 * @property string|null $pembatalan_periksa_dokter
 * @property string|null $pembayaran_per_unit
 * @property string|null $rekap_pembayaran_per_unit
 * @property string|null $grafik_kunjungan_percarabayar
 * @property string|null $ipsrs_pengadaan_pertanggal
 * @property string|null $ipsrs_stokkeluar_pertanggal
 * @property string|null $grafik_kunjungan_ranaptahun
 * @property string|null $pcare_cek_rujukan
 * @property string|null $grafik_lab_ralantahun
 * @property string|null $grafik_rad_ralantahun
 * @property string|null $cek_entry_ralan
 * @property string|null $inacbg_klaim_baru_manual2
 * @property string|null $permintaan_medis
 * @property string|null $rekap_permintaan_medis
 * @property string|null $surat_pemesanan_medis
 * @property string|null $permintaan_non_medis
 * @property string|null $rekap_permintaan_non_medis
 * @property string|null $surat_pemesanan_non_medis
 * @property string $grafik_per_perujuk
 * @property string|null $bpjs_cek_prosedur
 * @property string|null $bpjs_cek_kelas_rawat
 * @property string|null $bpjs_cek_dokter
 * @property string|null $bpjs_cek_spesialistik
 * @property string|null $bpjs_cek_ruangrawat
 * @property string|null $bpjs_cek_carakeluar
 * @property string|null $bpjs_cek_pasca_pulang
 * @property string|null $detail_tindakan_okvk
 * @property string|null $billing_parsial
 * @property string|null $bpjs_cek_nomor_rujukan_rs
 * @property string|null $bpjs_cek_rujukan_kartu_pcare
 * @property string|null $bpjs_cek_rujukan_kartu_rs
 * @property string|null $akses_depo_obat
 * @property string|null $bpjs_rujukan_keluar
 * @property string|null $grafik_lab_ralanbulan
 * @property string|null $pengeluaran_stok_apotek
 * @property string|null $grafik_rad_ralanbulan
 * @property string|null $detailjmdokter2
 * @property string|null $pengaduan_pasien
 * @property string|null $grafik_lab_ralanhari
 * @property string|null $grafik_rad_ralanhari
 * @property string|null $sensus_harian_ralan
 * @property string|null $metode_racik
 * @property string|null $pembayaran_akun_bayar
 * @property string|null $pengguna_obat_resep
 * @property string|null $rekap_pemesanan
 * @property string|null $master_berkas_pegawai
 * @property string|null $berkas_kepegawaian
 * @property string|null $riwayat_jabatan
 * @property string|null $riwayat_pendidikan
 * @property string|null $riwayat_naik_gaji
 * @property string|null $kegiatan_ilmiah
 * @property string|null $riwayat_penghargaan
 * @property string|null $riwayat_penelitian
 * @property string|null $penerimaan_non_medis
 * @property string|null $bayar_pesan_non_medis
 * @property string|null $hutang_barang_non_medis
 * @property string|null $rekap_pemesanan_non_medis
 * @property string|null $insiden_keselamatan
 * @property string|null $insiden_keselamatan_pasien
 * @property string|null $grafik_ikp_pertahun
 * @property string|null $grafik_ikp_perbulan
 * @property string|null $grafik_ikp_pertanggal
 * @property string|null $riwayat_data_batch
 * @property string|null $grafik_ikp_jenis
 * @property string|null $grafik_ikp_dampak
 * @property string|null $piutang_akun_piutang
 * @property string|null $grafik_kunjungan_per_agama
 * @property string|null $grafik_kunjungan_per_umur
 * @property string|null $suku_bangsa
 * @property string|null $bahasa_pasien
 * @property string|null $golongan_tni
 * @property string|null $satuan_tni
 * @property string|null $jabatan_tni
 * @property string|null $pangkat_tni
 * @property string|null $golongan_polri
 * @property string|null $satuan_polri
 * @property string|null $jabatan_polri
 * @property string|null $pangkat_polri
 * @property string|null $cacat_fisik
 * @property string|null $grafik_kunjungan_suku
 * @property string|null $grafik_kunjungan_bahasa
 * @property string|null $booking_operasi
 * @property string|null $mapping_poli_bpjs
 * @property string|null $grafik_kunjungan_per_cacat
 * @property string|null $barang_cssd
 * @property string|null $skdp_bpjs
 * @property string|null $booking_registrasi
 * @property string|null $bpjs_cek_propinsi
 * @property string|null $bpjs_cek_kabupaten
 * @property string|null $bpjs_cek_kecamatan
 * @property string|null $bpjs_cek_dokterdpjp
 * @property string|null $bpjs_cek_riwayat_rujukanrs
 * @property string|null $bpjs_cek_tanggal_rujukan
 * @property string|null $permintaan_lab
 * @property string|null $permintaan_radiologi
 * @property string|null $surat_indeks
 * @property string|null $surat_map
 * @property string|null $surat_almari
 * @property string|null $surat_rak
 * @property string|null $surat_ruang
 * @property string|null $surat_klasifikasi
 * @property string|null $surat_status
 * @property string|null $surat_sifat
 * @property string $surat_balas
 * @property string|null $surat_masuk
 * @property string|null $pcare_cek_dokter
 * @property string|null $pcare_cek_poli
 * @property string|null $pcare_cek_provider
 * @property string|null $pcare_cek_statuspulang
 * @property string|null $pcare_cek_spesialis
 * @property string|null $pcare_cek_subspesialis
 * @property string|null $pcare_cek_sarana
 * @property string|null $pcare_cek_khusus
 * @property string|null $pcare_cek_obat
 * @property string|null $pcare_cek_tindakan
 * @property string|null $pcare_cek_faskessubspesialis
 * @property string|null $pcare_cek_faskesalihrawat
 * @property string|null $pcare_cek_faskesthalasemia
 * @property string|null $pcare_mapping_obat
 * @property string|null $pcare_mapping_tindakan
 * @property string|null $pcare_club_prolanis
 * @property string|null $pcare_mapping_poli
 * @property string|null $pcare_kegiatan_kelompok
 * @property string|null $pcare_mapping_tindakan_ranap
 * @property string|null $pcare_peserta_kegiatan_kelompok
 * @property string|null $sirkulasi_obat3
 * @property string|null $bridging_pcare_daftar
 * @property string|null $pcare_mapping_dokter
 * @property string|null $ranap_per_ruang
 * @property string|null $penyakit_ranap_cara_bayar
 * @property string|null $anggota_militer_dirawat
 * @property string|null $set_input_parsial
 * @property string|null $lama_pelayanan_radiologi
 * @property string|null $lama_pelayanan_lab
 * @property string|null $bpjs_cek_sep
 * @property string|null $catatan_perawatan
 * @property string|null $surat_keluar
 * @property string|null $kegiatan_farmasi
 * @property string|null $stok_opname_logistik
 * @property string|null $sirkulasi_non_medis
 * @property string|null $rekap_lab_pertahun
 * @property string|null $perujuk_lab_pertahun
 * @property string|null $rekap_radiologi_pertahun
 * @property string|null $perujuk_radiologi_pertahun
 * @property string|null $jumlah_porsi_diet
 * @property string|null $jumlah_macam_diet
 * @property string|null $payment_point2
 * @property string|null $pembayaran_akun_bayar2
 * @property string|null $hapus_nota_salah
 * @property string|null $pengkajian_askep
 * @property string|null $hais_perbangsal
 * @property string|null $ppn_obat
 * @property string|null $saldo_akun_perbulan
 * @property string|null $display_apotek
 * @property string|null $sisrute_referensi_faskes
 * @property string|null $sisrute_referensi_alasanrujuk
 * @property string|null $sisrute_referensi_diagnosa
 * @property string|null $sisrute_rujukan_masuk
 * @property string|null $sisrute_rujukan_keluar
 * @property string|null $bpjs_cek_skdp
 * @property string|null $data_batch
 * @property string|null $kunjungan_permintaan_lab
 * @property string|null $kunjungan_permintaan_lab2
 * @property string|null $kunjungan_permintaan_radiologi
 * @property string|null $kunjungan_permintaan_radiologi2
 * @property string|null $pcare_pemberian_obat
 * @property string $pcare_pemberian_tindakan
 * @property string|null $pembayaran_akun_bayar3
 * @property string|null $password_asuransi
 * @property string $kemenkes_sitt
 * @property string|null $siranap_ketersediaan_kamar
 * @property string|null $grafik_tb_periodelaporan
 * @property string|null $grafik_tb_rujukan
 * @property string|null $grafik_tb_riwayat
 * @property string|null $grafik_tb_tipediagnosis
 * @property string|null $grafik_tb_statushiv
 * @property string|null $grafik_tb_skoringanak
 * @property string|null $grafik_tb_konfirmasiskoring5
 * @property string|null $grafik_tb_konfirmasiskoring6
 * @property string|null $grafik_tb_sumberobat
 * @property string|null $grafik_tb_hasilakhirpengobatan
 * @property string $grafik_tb_hasilteshiv
 * @property string $kadaluarsa_batch
 * @property string|null $sisa_stok
 * @property string|null $obat_per_resep
 * @property string|null $pemakaian_air_pdam
 * @property string|null $limbah_b3_medis
 * @property string|null $grafik_air_pdam_pertanggal
 * @property string|null $grafik_air_pdam_perbulan
 * @property string|null $grafik_limbahb3_pertanggal
 * @property string|null $grafik_limbahb3_perbulan
 * @property string|null $limbah_domestik
 * @property string|null $grafik_limbahdomestik_pertanggal
 * @property string|null $grafik_limbahdomestik_perbulan
 * @property string|null $mutu_air_limbah
 * @property string|null $pest_control
 * @property string|null $ruang_perpustakaan
 * @property string|null $kategori_perpustakaan
 * @property string|null $jenis_perpustakaan
 * @property string|null $pengarang_perpustakaan
 * @property string|null $penerbit_perpustakaan
 * @property string|null $koleksi_perpustakaan
 * @property string|null $inventaris_perpustakaan
 * @property string|null $set_peminjaman_perpustakaan
 * @property string|null $denda_perpustakaan
 * @property string|null $anggota_perpustakaan
 * @property string|null $peminjaman_perpustakaan
 * @property string|null $bayar_denda_perpustakaan
 * @property string|null $ebook_perpustakaan
 * @property string|null $jenis_cidera_k3rs
 * @property string|null $penyebab_k3rs
 * @property string|null $jenis_luka_k3rs
 * @property string|null $lokasi_kejadian_k3rs
 * @property string|null $dampak_cidera_k3rs
 * @property string|null $jenis_pekerjaan_k3rs
 * @property string|null $bagian_tubuh_k3rs
 * @property string|null $peristiwa_k3rs
 * @property string|null $grafik_k3_pertahun
 * @property string|null $grafik_k3_perbulan
 * @property string|null $grafik_k3_pertanggal
 * @property string|null $grafik_k3_perjeniscidera
 * @property string|null $grafik_k3_perpenyebab
 * @property string|null $grafik_k3_perjenisluka
 * @property string|null $grafik_k3_lokasikejadian
 * @property string|null $grafik_k3_dampakcidera
 * @property string|null $grafik_k3_perjenispekerjaan
 * @property string|null $grafik_k3_perbagiantubuh
 * @property string|null $jenis_cidera_k3rstahun
 * @property string|null $penyebab_k3rstahun
 * @property string|null $jenis_luka_k3rstahun
 * @property string|null $lokasi_kejadian_k3rstahun
 * @property string|null $dampak_cidera_k3rstahun
 * @property string|null $jenis_pekerjaan_k3rstahun
 * @property string|null $bagian_tubuh_k3rstahun
 * @property string|null $sekrining_rawat_jalan
 * @property string|null $bpjs_histori_pelayanan
 * @property string|null $rekap_mutasi_berkas
 * @property string|null $skrining_ralan_pernapasan_pertahun
 * @property string|null $pengajuan_barang_medis
 * @property string|null $pengajuan_barang_nonmedis
 * @property string|null $grafik_kunjungan_ranapbulan
 * @property string|null $grafik_kunjungan_ranaptanggal
 * @property string|null $grafik_kunjungan_ranap_peruang
 * @property string|null $kunjungan_bangsal_pertahun
 * @property string|null $grafik_jenjang_jabatanpegawai
 * @property string|null $grafik_bidangpegawai
 * @property string|null $grafik_departemenpegawai
 * @property string|null $grafik_pendidikanpegawai
 * @property string|null $grafik_sttswppegawai
 * @property string|null $grafik_sttskerjapegawai
 * @property string|null $grafik_sttspulangranap
 * @property string|null $kip_pasien_ranap
 * @property string|null $kip_pasien_ralan
 * @property string|null $bpjs_mapping_dokterdpjp
 * @property string|null $data_triase_igd
 * @property string|null $master_triase_skala1
 * @property string|null $master_triase_skala2
 * @property string|null $master_triase_skala3
 * @property string|null $master_triase_skala4
 * @property string|null $master_triase_skala5
 * @property string|null $master_triase_pemeriksaan
 * @property string|null $master_triase_macamkasus
 * @property string|null $rekap_permintaan_diet
 * @property string|null $daftar_pasien_ranap
 * @property string|null $daftar_pasien_ranaptni
 * @property string|null $pengajuan_asetinventaris
 * @property string|null $item_apotek_jenis
 * @property string|null $item_apotek_kategori
 * @property string|null $item_apotek_golongan
 * @property string|null $item_apotek_industrifarmasi
 * @property string|null $10_obat_terbanyak_poli
 * @property string|null $grafik_pengajuan_aset_urgensi
 * @property string|null $grafik_pengajuan_aset_status
 * @property string|null $grafik_pengajuan_aset_departemen
 * @property string|null $rekap_pengajuan_aset_departemen
 * @property string|null $grafik_kelompok_jabatanpegawai
 * @property string|null $grafik_resiko_kerjapegawai
 * @property string|null $grafik_emergency_indexpegawai
 * @property string|null $grafik_inventaris_ruang
 * @property string|null $harian_HAIs2
 * @property string|null $grafik_inventaris_jenis
 * @property string|null $data_resume_pasien
 * @property string|null $perkiraan_biaya_ranap
 * @property string|null $rekap_obat_poli
 * @property string|null $rekap_obat_pasien
 * @property string|null $permintaan_perbaikan_inventaris
 * @property string|null $grafik_HAIs_pasienbangsal
 * @property string|null $grafik_HAIs_pasienbulan
 * @property string|null $grafik_HAIs_laju_vap
 * @property string|null $grafik_HAIs_laju_iad
 * @property string|null $grafik_HAIs_laju_pleb
 * @property string|null $grafik_HAIs_laju_isk
 * @property string|null $grafik_HAIs_laju_ilo
 * @property string|null $grafik_HAIs_laju_hap
 * @property string|null $inhealth_mapping_poli
 * @property string|null $inhealth_mapping_dokter
 * @property string|null $inhealth_mapping_tindakan_ralan
 * @property string|null $inhealth_mapping_tindakan_ranap
 * @property string|null $inhealth_mapping_tindakan_radiologi
 * @property string|null $inhealth_mapping_tindakan_laborat
 * @property string|null $inhealth_mapping_tindakan_operasi
 * @property string|null $hibah_obat_bhp
 * @property string|null $asal_hibah
 * @property string|null $asuhan_gizi
 * @property string|null $inhealth_kirim_tagihan
 * @property string|null $sirkulasi_obat4
 * @property string|null $sirkulasi_obat5
 * @property string|null $sirkulasi_non_medis2
 * @property string|null $monitoring_asuhan_gizi
 * @property string|null $penerimaan_obat_perbulan
 * @property string|null $rekap_kunjungan
 * @property string|null $surat_sakit
 * @property string|null $penilaian_awal_keperawatan_ralan
 * @property string|null $permintaan_diet
 * @property string|null $master_masalah_keperawatan
 * @property string|null $pengajuan_cuti
 * @property string|null $kedatangan_pasien
 * @property string|null $utd_pendonor
 * @property string|null $toko_suplier
 * @property string|null $toko_jenis
 * @property string|null $toko_set_harga
 * @property string|null $toko_barang
 * @property string|null $penagihan_piutang_pasien
 * @property string|null $akun_penagihan_piutang
 * @property string|null $stok_opname_toko
 * @property string|null $toko_riwayat_barang
 * @property string|null $toko_surat_pemesanan
 * @property string|null $toko_pengajuan_barang
 * @property string|null $toko_penerimaan_barang
 * @property string|null $toko_pengadaan_barang
 * @property string|null $toko_hutang
 * @property string|null $toko_bayar_pemesanan
 * @property string|null $toko_member
 * @property string|null $toko_penjualan
 * @property string|null $registrasi_poli_per_tanggal
 * @property string|null $toko_piutang
 * @property string|null $toko_retur_beli
 * @property string|null $ipsrs_returbeli
 * @property string|null $ipsrs_riwayat_barang
 * @property string|null $pasien_corona
 * @property string|null $toko_pendapatan_harian
 * @property string|null $diagnosa_pasien_corona
 * @property string|null $perawatan_pasien_corona
 * @property string|null $penilaian_awal_keperawatan_gigi
 * @property string|null $master_masalah_keperawatan_gigi
 * @property string|null $toko_bayar_piutang
 * @property string|null $toko_piutang_harian
 * @property string|null $toko_penjualan_harian
 * @property string|null $deteksi_corona
 * @property string|null $penilaian_awal_keperawatan_kebidanan
 * @property string|null $pengumuman_epasien
 * @property string|null $surat_hamil
 * @property string|null $set_tarif_online
 * @property string|null $booking_periksa
 * @property string|null $toko_sirkulasi
 * @property string|null $toko_retur_jual
 * @property string|null $toko_retur_piutang
 * @property string|null $toko_sirkulasi2
 * @property string|null $toko_keuntungan_barang
 * @property string|null $zis_pengeluaran_penerima_dankes
 * @property string|null $zis_penghasilan_penerima_dankes
 * @property string|null $zis_ukuran_rumah_penerima_dankes
 * @property string|null $zis_dinding_rumah_penerima_dankes
 * @property string|null $zis_lantai_rumah_penerima_dankes
 * @property string|null $zis_atap_rumah_penerima_dankes
 * @property string|null $zis_kepemilikan_rumah_penerima_dankes
 * @property string|null $zis_kamar_mandi_penerima_dankes
 * @property string|null $zis_dapur_rumah_penerima_dankes
 * @property string|null $zis_kursi_rumah_penerima_dankes
 * @property string|null $zis_kategori_phbs_penerima_dankes
 * @property string|null $zis_elektronik_penerima_dankes
 * @property string|null $zis_ternak_penerima_dankes
 * @property string|null $zis_jenis_simpanan_penerima_dankes
 * @property string|null $penilaian_awal_keperawatan_anak
 * @property string|null $zis_kategori_asnaf_penerima_dankes
 * @property string|null $master_masalah_keperawatan_anak
 * @property string|null $master_imunisasi
 * @property string|null $zis_patologis_penerima_dankes
 * @property string|null $pcare_cek_kartu
 * @property string|null $surat_bebas_narkoba
 * @property string|null $surat_keterangan_covid
 * @property string|null $pemakaian_air_tanah
 * @property string|null $grafik_air_tanah_pertanggal
 * @property string|null $grafik_air_tanah_perbulan
 * @property string|null $lama_pelayanan_poli
 * @property string|null $hemodialisa
 * @property string|null $laporan_tahunan_irj
 * @property string|null $grafik_harian_hemodialisa
 * @property string|null $grafik_bulanan_hemodialisa
 * @property string|null $grafik_tahunan_hemodialisa
 * @property string|null $grafik_bulanan_meninggal
 * @property string|null $perbaikan_inventaris
 * @property string|null $surat_cuti_hamil
 * @property string|null $permintaan_stok_obat_pasien
 * @property string|null $pemeliharaan_inventaris
 * @property string|null $klasifikasi_pasien_ranap
 * @property string|null $bulanan_klasifikasi_pasien_ranap
 * @property string|null $harian_klasifikasi_pasien_ranap
 * @property string|null $klasifikasi_pasien_perbangsal
 * @property string|null $soap_perawatan
 * @property string|null $klaim_rawat_jalan
 * @property string|null $skrining_gizi
 * @property string|null $lama_penyiapan_rm
 * @property string|null $dosis_radiologi
 * @property string|null $demografi_umur_kunjungan
 * @property string|null $jam_diet_pasien
 * @property string|null $rvu_bpjs
 * @property string|null $verifikasi_penerimaan_farmasi
 * @property string|null $verifikasi_penerimaan_logistik
 * @property string|null $pemeriksaan_lab_pa
 * @property string|null $ringkasan_pengajuan_obat
 * @property string|null $ringkasan_pemesanan_obat
 * @property string|null $ringkasan_pengadaan_obat
 * @property string|null $ringkasan_penerimaan_obat
 * @property string|null $ringkasan_hibah_obat
 * @property string|null $ringkasan_penjualan_obat
 * @property string|null $ringkasan_beri_obat
 * @property string|null $ringkasan_piutang_obat
 * @property string|null $ringkasan_stok_keluar_obat
 * @property string|null $ringkasan_retur_suplier_obat
 * @property string|null $ringkasan_retur_pembeli_obat
 * @property string|null $penilaian_awal_keperawatan_ranapkebidanan
 * @property string|null $ringkasan_pengajuan_nonmedis
 * @property string|null $ringkasan_pemesanan_nonmedis
 * @property string|null $ringkasan_pengadaan_nonmedis
 * @property string|null $ringkasan_penerimaan_nonmedis
 * @property string|null $ringkasan_stokkeluar_nonmedis
 * @property string|null $ringkasan_returbeli_nonmedis
 * @property string|null $omset_penerimaan
 * @property string|null $validasi_penagihan_piutang
 * @property string|null $permintaan_ranap
 * @property string|null $bpjs_diagnosa_prb
 * @property string|null $bpjs_obat_prb
 * @property string|null $bpjs_surat_kontrol
 * @property string|null $penggunaan_bhp_ok
 * @property string|null $surat_keterangan_rawat_inap
 * @property string|null $surat_keterangan_sehat
 * @property string|null $pendapatan_per_carabayar
 * @property string|null $akun_host_to_host_bank_jateng
 * @property string|null $pembayaran_bank_jateng
 * @property string|null $bpjs_surat_pri
 * @property string|null $ringkasan_tindakan
 * @property string|null $lama_pelayanan_pasien
 * @property string|null $surat_sakit_pihak_2
 * @property string|null $tagihan_hutang_obat
 * @property string|null $referensi_mobilejkn_bpjs
 * @property string|null $batal_pendaftaran_mobilejkn_bpjs
 * @property string|null $lama_operasi
 * @property string|null $grafik_inventaris_kategori
 * @property string|null $grafik_inventaris_merk
 * @property string|null $grafik_inventaris_produsen
 * @property string|null $pengembalian_deposit_pasien
 * @property string|null $validasi_tagihan_hutang_obat
 * @property string|null $piutang_obat_belum_lunas
 * @property string|null $integrasi_briapi
 * @property string|null $pengadaan_aset_inventaris
 * @property string|null $akun_aset_inventaris
 * @property string|null $suplier_inventaris
 * @property string|null $penerimaan_aset_inventaris
 * @property string|null $bayar_pemesanan_iventaris
 * @property string|null $hutang_aset_inventaris
 * @property string|null $hibah_aset_inventaris
 * @property string|null $titip_faktur_non_medis
 * @property string|null $validasi_tagihan_non_medis
 * @property string|null $titip_faktur_aset
 * @property string|null $validasi_tagihan_aset
 * @property string|null $hibah_non_medis
 * @property string|null $pcare_alasan_tacc
 * @property string|null $resep_luar
 * @property string|null $surat_bebas_tbc
 * @property string|null $surat_buta_warna
 * @property string|null $surat_bebas_tato
 * @property string|null $surat_kewaspadaan_kesehatan
 * @property string|null $grafik_porsidiet_pertanggal
 * @property string|null $grafik_porsidiet_perbulan
 * @property string|null $grafik_porsidiet_pertahun
 * @property string|null $grafik_porsidiet_perbangsal
 * @property string|null $penilaian_awal_medis_ralan
 * @property string|null $master_masalah_keperawatan_mata
 * @property string|null $penilaian_awal_keperawatan_mata
 * @property string|null $penilaian_awal_medis_ranap
 * @property string|null $penilaian_awal_medis_ranap_kebidanan
 * @property string|null $penilaian_awal_medis_ralan_kebidanan
 * @property string|null $penilaian_awal_medis_igd
 * @property string|null $penilaian_awal_medis_ralan_anak
 * @property string|null $bpjs_referensi_poli_hfis
 * @property string|null $bpjs_referensi_dokter_hfis
 * @property string|null $bpjs_referensi_jadwal_hfis
 * @property string|null $penilaian_fisioterapi
 * @property string|null $bpjs_program_prb
 * @property string|null $bpjs_suplesi_jasaraharja
 * @property string|null $bpjs_data_induk_kecelakaan
 * @property string|null $bpjs_sep_internal
 * @property string|null $bpjs_klaim_jasa_raharja
 * @property string|null $bpjs_daftar_finger_print
 * @property string|null $bpjs_rujukan_khusus
 * @property string|null $pemeliharaan_gedung
 * @property string|null $grafik_perbaikan_inventaris_pertanggal
 * @property string|null $grafik_perbaikan_inventaris_perbulan
 * @property string|null $grafik_perbaikan_inventaris_pertahun
 * @property string|null $grafik_perbaikan_inventaris_perpelaksana_status
 * @property string|null $penilaian_mcu
 * @property string $peminjam_piutang
 * @property string|null $piutang_lainlain
 * @property string|null $cara_bayar
 * @property string|null $audit_kepatuhan_apd
 * @property string|null $bpjs_task_id
 * @property string|null $bayar_piutang_lain
 * @property string|null $pembayaran_akun_bayar4
 * @property string|null $stok_akhir_farmasi_pertanggal
 * @property string|null $riwayat_kamar_pasien
 * @property string|null $uji_fungsi_kfr
 * @property string|null $hapus_berkas_digital_perawatan
 * @property string|null $kategori_pengeluaran_harian
 * @property string|null $kategori_pemasukan_lain
 * @property string|null $pembayaran_akun_bayar5
 * @property string|null $ruang_ok
 * @property string|null $telaah_resep
 * @property string|null $jasa_tindakan_pasien
 * @property string|null $permintaan_resep_pulang
 * @property string|null $rekap_jm_dokter
 * @property string|null $status_data_rm
 * @property string|null $ubah_petugas_lab_pk
 * @property string|null $ubah_petugas_lab_pa
 * @property string|null $ubah_petugas_radiologi
 * @property string|null $gabung_norawat
 * @property string|null $gabung_rm
 * @property string|null $ringkasan_biaya_obat_pasien_pertanggal
 * @property string|null $master_masalah_keperawatan_igd
 * @property string|null $penilaian_awal_keperawatan_igd
 * @property string|null $bpjs_referensi_dpho_apotek
 * @property string|null $bpjs_referensi_poli_apotek
 * @property string|null $bayar_jm_dokter
 * @property string|null $bpjs_referensi_faskes_apotek
 * @property string|null $bpjs_referensi_spesialistik_apotek
 * @property string|null $pembayaran_briva
 * @property string|null $penilaian_awal_keperawatan_ranap
 * @property string|null $nilai_penerimaan_vendor_farmasi_perbulan
 * @property string|null $akun_bayar_hutang
 * @property string|null $master_rencana_keperawatan
 * @property string|null $laporan_tahunan_igd
 * @property string|null $obat_bhp_tidakbergerak
 * @property string|null $ringkasan_hutang_vendor_farmasi
 * @property string|null $nilai_penerimaan_vendor_nonmedis_perbulan
 * @property string|null $ringkasan_hutang_vendor_nonmedis
 * @property string|null $master_rencana_keperawatan_anak
 * @property string|null $anggota_polri_dirawat
 * @property string|null $daftar_pasien_ranap_polri
 * @property string|null $soap_ralan_polri
 * @property string|null $soap_ranap_polri
 * @property string|null $laporan_penyakit_polri
 * @property string|null $jumlah_pengunjung_ralan_polri
 * @property string|null $catatan_observasi_igd
 * @property string|null $catatan_observasi_ranap
 * @property string|null $catatan_observasi_ranap_kebidanan
 * @property string|null $catatan_observasi_ranap_postpartum
 * @property string|null $penilaian_awal_medis_ralan_tht
 * @property string|null $penilaian_psikologi
 * @property string|null $audit_cuci_tangan_medis
 * @property string|null $audit_pembuangan_limbah
 * @property string|null $ruang_audit_kepatuhan
 * @property string|null $audit_pembuangan_benda_tajam
 * @property string|null $audit_penanganan_darah
 * @property string|null $audit_pengelolaan_linen_kotor
 * @property string|null $audit_penempatan_pasien
 * @property string|null $audit_kamar_jenazah
 * @property string|null $audit_bundle_iadp
 * @property string|null $audit_bundle_ido
 * @property string|null $audit_fasilitas_kebersihan_tangan
 * @property string|null $audit_fasilitas_apd
 * @property string|null $audit_pembuangan_limbah_cair_infeksius
 * @property string|null $audit_sterilisasi_alat
 * @property string|null $penilaian_awal_medis_ralan_psikiatri
 * @property string|null $persetujuan_penolakan_tindakan
 * @property string|null $audit_bundle_isk
 * @property string|null $audit_bundle_plabsi
 * @property string|null $audit_bundle_vap
 * @property string|null $akun_host_to_host_bank_papua
 * @property string|null $pembayaran_bank_papua
 * @property string|null $penilaian_awal_medis_ralan_penyakit_dalam
 * @property string|null $penilaian_awal_medis_ralan_mata
 * @property string|null $penilaian_awal_medis_ralan_neurologi
 * @property string|null $sirkulasi_obat6
 * @property string|null $penilaian_awal_medis_ralan_orthopedi
 * @property string|null $penilaian_awal_medis_ralan_bedah
 * @property string|null $integrasi_khanza_health_services
 * @property string|null $soap_ralan_tni
 * @property string|null $soap_ranap_tni
 * @property string|null $jumlah_pengunjung_ralan_tni
 * @property string|null $laporan_penyakit_tni
 * @property string|null $catatan_keperawatan_ranap
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User where10ObatTerbanyakPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAksesDepoObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunAsetInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunBayarHutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunHostToHostBankJateng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunHostToHostBankPapua($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunPenagihanPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAkunRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAnggotaMiliterDirawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAnggotaPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAnggotaPolriDirawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAplicareKetersediaanKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAplicareReferensiKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAsalHibah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAsuhanGizi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditBundleIadp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditBundleIdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditBundleIsk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditBundlePlabsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditBundleVap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditCuciTanganMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditFasilitasApd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditFasilitasKebersihanTangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditKamarJenazah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditKepatuhanApd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPembuanganBendaTajam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPembuanganLimbah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPembuanganLimbahCairInfeksius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPenangananDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPenempatanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditPengelolaanLinenKotor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuditSterilisasiAlat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBagianTubuhK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBagianTubuhK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBahasaPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBarangCssd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBarcoderalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBarcoderanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBatalPendaftaranMobilejknBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarDendaPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarJmDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarPemesananIventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarPemesananObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarPesanNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBayarPiutangLain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBeriObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBerkasDigitalPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBerkasKepegawaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBiayaHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBiayaMasukSekali($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBillingParsial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBillingRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBillingRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBookingOperasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBookingPeriksa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBookingRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekCarakeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekDokterdpjp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekKabupaten($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekKartu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekKelasRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekNomorRujukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekNomorRujukanRs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekPascaPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekPropinsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekProsedur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekRiwayat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekRiwayatRujukanrs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekRuangrawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekRujukanKartuPcare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekRujukanKartuRs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekSep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekSkdp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekSpesialistik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsCekTanggalRujukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsDaftarFingerPrint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsDataIndukKecelakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsDiagnosaPrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsHistoriPelayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsKlaimJasaRaharja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsMappingDokterdpjp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsMonitoringKlaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsObatPrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsProgramPrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiDiagnosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiDokterHfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiDphoApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiFaskes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiFaskesApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiJadwalHfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiPoliApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiPoliHfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsReferensiSpesialistikApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsRujukanKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsRujukanKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsSep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsSepInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsSuplesiJasaraharja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsSuratKontrol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsSuratPri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBpjsTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBridgingPcareDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBukuBesar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananHAIs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananJs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananKlasifikasiPasienRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananPaketBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBulananParamedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCacatFisik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCaraBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCashflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanKeperawatanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanObservasiIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanObservasiRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanObservasiRanapKebidanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanObservasiRanapPostpartum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCatatanPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCekEntryRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereClosingKasir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDaftarPasienRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDaftarPasienRanapPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDaftarPasienRanaptni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDampakCideraK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDampakCideraK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDaruratStok($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDataBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDataHAIs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDataResumePasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDataTriaseIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDemografiUmurKunjungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDendaPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepositPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDetailPiutangPenjab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDetailTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDetailTindakanOkvk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDetailjmdokter2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeteksiCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDiagnosaPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDiagnosaPasienCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDietPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDisplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDisplayApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDosisRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDpjpRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEbookPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEditRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFeeBacaanEkg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFeeRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFeeRujukanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFeeRujukanRontgen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFeeVisitDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGabungNorawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGabungRm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGolonganBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGolonganPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGolonganTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikAirPdamPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikAirPdamPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikAirTanahPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikAirTanahPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikBidangpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikBulananHemodialisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikBulananMeninggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikDepartemenpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikEmergencyIndexpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuHap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuIad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuIlo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuIsk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuPleb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsLajuVap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsPasienbangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHAIsPasienbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikHarianHemodialisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikIkpDampak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikIkpJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikIkpPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikIkpPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikIkpPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikInventarisJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikInventarisKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikInventarisMerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikInventarisProdusen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikInventarisRuang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikJenjangJabatanpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Dampakcidera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Lokasikejadian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perbagiantubuh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perjeniscidera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perjenisluka($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perjenispekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Perpenyebab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Pertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikK3Pertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKelompokJabatanpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganBahasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganDemografi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerCacat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerUmur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPercarabayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerdokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerpekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPerpendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganRanapPeruang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganRanapbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganRanaptahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganRanaptanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusbatalbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusbataltahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusbataltanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftarbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftarbulan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftartahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftartahun2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftartanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganStatusdaftartanggal2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikKunjunganSuku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLabRalanbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLabRalanhari($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLabRalantahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLimbahb3Perbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLimbahb3Pertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLimbahdomestikPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikLimbahdomestikPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPendidikanpegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPengajuanAsetDepartemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPengajuanAsetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPengajuanAsetUrgensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPerPerujuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPerbaikanInventarisPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPerbaikanInventarisPerpelaksanaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPerbaikanInventarisPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPerbaikanInventarisPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPorsidietPerbangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPorsidietPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPorsidietPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikPorsidietPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikRadRalanbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikRadRalanhari($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikRadRalantahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikResikoKerjapegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikSttskerjapegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikSttspulangranap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikSttswppegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTahunanHemodialisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbHasilakhirpengobatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbHasilteshiv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbKonfirmasiskoring5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbKonfirmasiskoring6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbPeriodelaporan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbRiwayat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbRujukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbSkoringanak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbStatushiv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbSumberobat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrafikTbTipediagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHaisPerbangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHapusBerkasDigitalPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHapusNotaSalah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianHAIs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianHAIs2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianJs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianKlasifikasiPasienRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianKso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianMenejemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianPaketBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianParamedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianTindakanDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHarianTindakanPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHemodialisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHibahAsetInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHibahNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHibahObatBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHitungAlos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHitungBor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHutangAsetInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHutangBarangNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHutangObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIcd9($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInacbgCoderNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInacbgKlaimBaruManual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInacbgKlaimBaruManual2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInacbgKlaimBaruOtomatis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIndustrifarmasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInformasiKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthCekEligibilitas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthKirimTagihan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingTindakanLaborat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingTindakanOperasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingTindakanRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingTindakanRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthMappingTindakanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthReferensiFaskes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthReferensiJenpelRuangRawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthReferensiPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInhealthSjp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInsidenKeselamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInsidenKeselamatanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIntegrasiBriapi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIntegrasiKhanzaHealthServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisKoleksi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisMerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisProdusen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisRuang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInventarisSirkulasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsJenisBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsPengadaanBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsPengadaanPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsPengeluaranHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsRekapPengadaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsRekapStokKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsReturbeli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsRiwayatBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsStokKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIpsrsStokkeluarPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereItemApotekGolongan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereItemApotekIndustrifarmasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereItemApotekJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereItemApotekKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJabatanPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJabatanTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJadwalPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJadwalPraktek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJamDietPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJasaTindakanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisCideraK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisCideraK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisLukaK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisLukaK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisPekerjaanK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisPekerjaanK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJenisPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJmRanapDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJumlahMacamDiet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJumlahPengunjungRalanPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJumlahPengunjungRalanTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJumlahPorsiDiet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJurnalHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKadaluarsaBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKamarInap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKasirRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKategoriBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKategoriPemasukanLain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKategoriPengeluaranHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKategoriPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKedatanganPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKegiatanFarmasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKegiatanIlmiah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKelahiranBayi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKemenkesSitt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeterlambatanPresensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeuntunganBeriObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeuntunganBeriObatNonpiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeuntunganPenjualan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKipPasienRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKipPasienRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKlaimRawatJalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKlasifikasiPasienPerbangsal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKlasifikasiPasienRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKoleksiPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKonversiSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganBangsalPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganPermintaanLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganPermintaanLab2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganPermintaanRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganPermintaanRadiologi2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKunjunganRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaOperasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPelayananRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLamaPenyiapanRm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLaporanPenyakitPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLaporanPenyakitTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLaporanTahunanIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLaporanTahunanIrj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLimbahB3Medis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLimbahDomestik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLokasiKejadianK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLokasiKejadianK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMappingPoliBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterBerkasPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterImunisasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterMasalahKeperawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterMasalahKeperawatanAnak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterMasalahKeperawatanGigi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterMasalahKeperawatanIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterMasalahKeperawatanMata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterRencanaKeperawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterRencanaKeperawatanAnak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseMacamkasus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriasePemeriksaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseSkala1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseSkala2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseSkala3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseSkala4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMasterTriaseSkala5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMetodeRacik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMonitoringAsuhanGizi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMutasiBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMutasiBerkas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMutuAirLimbah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNilaiPenerimaanVendorFarmasiPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNilaiPenerimaanVendorNonmedisPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatBhpTidakbergerak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerCaraBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerDokterPeresep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerDokterRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerDokterRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereObatPerResep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOmsetPenerimaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOperasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePangkatPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePangkatTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirRekapBulanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParkirRekapHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePasienCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePasienMeninggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePasswordAsuransi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaymentPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaymentPoint2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareAlasanTacc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekFaskesalihrawat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekFaskessubspesialis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekFaskesthalasemia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekKartu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekKesadaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekPenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekRujukan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekSarana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekSpesialis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekStatuspulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekSubspesialis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareCekTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareClubProlanis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareKegiatanKelompok($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareMappingDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareMappingObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareMappingPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareMappingTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcareMappingTindakanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcarePemberianObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcarePemberianTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePcarePesertaKegiatanKelompok($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePegawaiAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePegawaiUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemakaianAirPdam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemakaianAirTanah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemasukanLain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembatalanPeriksaDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranAkunBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranAkunBayar2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranAkunBayar3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranAkunBayar4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranAkunBayar5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranBankJateng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranBankPapua($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranBriva($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePembayaranRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemberianObatPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemeliharaanGedung($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemeliharaanInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemeriksaanLabPa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePemesananObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeminjamPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeminjamanBerkas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeminjamanPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenagihanPiutangPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePendapatanPerCarabayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenerbitPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenerimaanAsetInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenerimaanNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenerimaanObatPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengadaanAsetInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengadaanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengaduanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengajuanAsetinventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengajuanBarangMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengajuanBarangNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengajuanCuti($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengambilanPenunjangUtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengambilanPenunjangUtd2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengambilanUtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengambilanUtd2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengarangPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengaturanRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengeluaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengeluaranStokApotek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengembalianDepositPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenggunaObatResep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenggunaanBhpOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengkajianAskep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePengumumanEpasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanAnak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanGigi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanKebidanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanMata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalKeperawatanRanapkebidanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisIgd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanAnak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanBedah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanKebidanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanMata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanNeurologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanOrthopedi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanPenyakitDalam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanPsikiatri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRalanTht($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianAwalMedisRanapKebidanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianFisioterapi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianMcu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenilaianPsikologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenjualanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenjualanObatPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitMenularRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitMenularRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitPd3i($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyakitRanapCaraBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyebabK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePenyebabK3rstahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerawatanPasienCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerbaikanInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeriksaLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeriksaRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePeristiwaK3rs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerkiraanBiayaRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanDiet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanPerbaikanInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanResepPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePermintaanStokObatPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePersetujuanPenolakanTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerujukLabPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerujukRadiologiPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerusahaanPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePestControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePetugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangAkunPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangLainlain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangObatBelumLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangPasien2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePiutangRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePnyTakmenularRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePnyTakmenularRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePostingJurnal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePotonganBiaya($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePpnObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePresensiBulanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePresensiHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRanapPerRuang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReferensiMobilejknBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegistrasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegistrasiPoliPerTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapJmDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapKunjungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapLabPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapMutasiBerkas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapObatPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapObatPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPembayaranPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPembayaranRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPembayaranRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPemesanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPemesananNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPengajuanAsetDepartemen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPerShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPermintaanDiet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPermintaanMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPermintaanNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapPoliAnak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekapRadiologiPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRekeningTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReklasifikasiRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReklasifikasiRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResepDokter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResepLuar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResepObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResepPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResumePasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRetensiRm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReturDariPembeli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReturKeSuplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReturObatRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReturPiutangPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRincianPiutangPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanBeriObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanBiayaObatPasienPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanHibahObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanHutangVendorFarmasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanHutangVendorNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPemesananNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPemesananObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPenerimaanNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPenerimaanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPengadaanNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPengadaanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPengajuanNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPengajuanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPenjualanObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanPiutangObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanReturPembeliObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanReturSuplierObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanReturbeliNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanStokKeluarObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanStokkeluarNonmedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRingkasanTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatDataBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatKamarPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatNaikGaji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatObatAlkesBhp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatPenelitian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRiwayatPenghargaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl32($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl33($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl34($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl36($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl37($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl38($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl4a($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl4asebab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl4b($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRl4bsebab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRuangAuditKepatuhan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRuangOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRuangPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRujukanKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRujukanMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRujukanPoliInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRvuBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSaldoAkunPerbulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSatuanBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSatuanPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSatuanTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSekriningRawatJalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSensusHarianPoli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSensusHarianRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetHargaKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetHargaObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetHargaObatRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetHargaObatRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetInputParsial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetNoRm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetOtoRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetPeminjamanPerpustakaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetPenggunaanTarif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetTarifOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetupEmbalase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetupJamKamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetupOtolokasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSetupPjlab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSidikjari($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSiranapKetersediaanKamar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiNonMedis2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSirkulasiObat6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisaStok($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisruteReferensiAlasanrujuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisruteReferensiDiagnosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisruteReferensiFaskes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisruteRujukanKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSisruteRujukanMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSkdpBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSkriningGizi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSkriningRalanPernapasanPertahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSoapPerawatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSoapRalanPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSoapRalanTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSoapRanapPolri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSoapRanapTni($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatusDataRm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStokAkhirFarmasiPertanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStokObatPasien($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStokOpnameLogistik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStokOpnameObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStokOpnameToko($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSukuBangsa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuplierInventaris($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuplierPenunjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratAlmari($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratBalas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratBebasNarkoba($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratBebasTato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratBebasTbc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratButaWarna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratCutiHamil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratHamil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratIndeks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKeteranganCovid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKeteranganRawatInap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKeteranganSehat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKewaspadaanKesehatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratKlasifikasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratPemesananMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratPemesananNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratRak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratRuang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratSakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratSakitPihak2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratSifat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuratStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSurveilansPd3i($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSurveilansRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSurveilansRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTagihanHutangObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTagihanMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTambahanBiaya($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifOperasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTarifUtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTelaahResep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTemporaryPresensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTindakanRalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTindakanRanap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTitipFakturAset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTitipFakturNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoBayarPemesanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoBayarPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoHutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoKeuntunganBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPendapatanHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPenerimaanBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPengadaanBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPengajuanBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPenjualan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPenjualanHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoPiutangHarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoReturBeli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoReturJual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoReturPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoRiwayatBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoSetHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoSirkulasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoSirkulasi2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoSuplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokoSuratPemesanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTracerLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUbahPetugasLabPa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUbahPetugasLabPk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUbahPetugasRadiologi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUjiFungsiKfr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdCekalDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdDonor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdKomponenDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdMedisRusak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdPemisahanDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdPendonor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdPenunjangRusak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdPenyerahanDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUtdStokDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereValidasiPenagihanPiutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereValidasiTagihanAset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereValidasiTagihanHutangObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereValidasiTagihanNonMedis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerifikasiPenerimaanFarmasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerifikasiPenerimaanLogistik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisAtapRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisDapurRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisDindingRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisElektronikPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisJenisSimpananPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisKamarMandiPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisKategoriAsnafPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisKategoriPhbsPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisKepemilikanRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisKursiRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisLantaiRumahPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisPatologisPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisPengeluaranPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisPenghasilanPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisTernakPenerimaDankes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZisUkuranRumahPenerimaDankes($value)
 */
	class User extends \Eloquent {}
}

