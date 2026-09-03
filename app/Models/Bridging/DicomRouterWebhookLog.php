<?php

namespace App\Models\Bridging;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DicomRouterWebhookLog extends Model
{
    /**
     * Stage yang dikirim DICOM Router beserta label tampilannya. `dicom_sent`
     * dan `dicom_send_failed` berasal dari push worker, tiga sisanya dari
     * association handler dan tidak pernah membawa `imagingStudyId`.
     *
     * @var array<string, string>
     */
    public const STAGES = [
        'dicom_sent'                 => 'DICOM Terkirim',
        'dicom_send_failed'          => 'Gagal Kirim DICOM',
        'metadata_lookup_failed'     => 'Gagal Ambil Metadata',
        'imagingstudy_create_failed' => 'Gagal Membuat ImagingStudy',
        'imagingstudy_post_failed'   => 'Gagal Kirim ImagingStudy',
    ];

    protected $connection = 'mysql_smc';

    protected $table = 'dicom_router_webhook_logs';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $casts = [
        'status'         => 'boolean',
        'payload'        => 'array',
        'delivery_count' => 'integer',
    ];

    protected $searchColumns = [
        'dicom_router_webhook_logs.stage',
        'dicom_router_webhook_logs.message',
        'dicom_router_webhook_logs.accession_number',
        'dicom_router_webhook_logs.noorder',
        'dicom_router_webhook_logs.imaging_study_id',
        'dicom_router_webhook_logs.study_instance_uid',
        'dicom_router_webhook_logs.error_code',
        'dicom_router_webhook_logs.error_message',
    ];

    /**
     * Mencatat satu kiriman webhook. Aman dipanggil berulang untuk body yang
     * sama: kiriman ulang lewat tombol resend di dashboard router hanya
     * menaikkan delivery_count dan menggeser updated_at.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function rekamPengiriman(array $payload): self
    {
        $data = Arr::wrap(Arr::get($payload, 'data', []));
        $error = Arr::wrap(Arr::get($payload, 'error', []));

        $accessionNumber = static::asString(Arr::get($data, 'accessionNumber'), 20);

        $atribut = [
            'status'             => (bool) Arr::get($payload, 'status', false),
            'stage'              => static::asString(Arr::get($payload, 'stage'), 50) ?? 'unknown',
            'message'            => static::asString(Arr::get($payload, 'message'), 255),
            'organization_id'    => static::asString(Arr::get($data, 'organizationId'), 50),
            'imaging_study_id'   => static::asString(Arr::get($data, 'imagingStudyId'), 64),
            'accession_number'   => $accessionNumber,
            'noorder'            => static::noorderDariAccession($accessionNumber),
            'study_instance_uid' => static::asString(Arr::get($data, 'studyInstanceUID'), 64),
            'error_code'         => static::asString(Arr::get($error, '0.code'), 50),
            'error_message'      => static::asString(Arr::get($error, '0.message')),
            'payload'            => $payload,
        ];

        $log = static::query()->firstOrNew(['signature' => static::sidikJari($atribut)], $atribut);

        $log->delivery_count = $log->exists ? $log->delivery_count + 1 : 1;

        $log->save();

        return $log;
    }

    /**
     * Mengembalikan noorder permintaan radiologi dari accession number.
     *
     * Accession number dibentuk di sisi Khanza sebagai noorder tanpa awalan PR
     * ditambah indeks 2 digit atas pemeriksaan dalam order tersebut, jadi
     * pemetaan baliknya cukup memotong 2 digit terakhir. Nilainya hanya kolom
     * bantu: kalau formatnya berubah, join ke Khanza sekadar tidak ketemu.
     */
    public static function noorderDariAccession(?string $accessionNumber): ?string
    {
        if (empty($accessionNumber) || strlen($accessionNumber) <= 2) {
            return null;
        }

        return 'PR'.substr($accessionNumber, 0, -2);
    }

    /**
     * Ringkasan berhasil/gagal untuk periode dan filter yang sama.
     *
     * @return array{total: int, berhasil: int, gagal: int}
     */
    public static function ringkasanPengiriman(string $tglAwal = '', string $tglAkhir = '', string $stage = 'semua', string $hasil = 'semua'): array
    {
        // select() (bukan selectRaw) supaya daftar kolom dari scope ikut diganti.
        $ringkasan = static::query()
            ->laporanPengirimanDicom($tglAwal, $tglAkhir, $stage, $hasil)
            ->reorder()
            ->select(DB::raw('count(*) total, sum(dicom_router_webhook_logs.status = 1) berhasil, sum(dicom_router_webhook_logs.status = 0) gagal'))
            ->first();

        return [
            'total'    => (int) ($ringkasan->total ?? 0),
            'berhasil' => (int) ($ringkasan->berhasil ?? 0),
            'gagal'    => (int) ($ringkasan->gagal ?? 0),
        ];
    }

    public function scopeLaporanPengirimanDicom(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $stage = 'semua',
        string $hasil = 'semua'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sik = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            dicom_router_webhook_logs.id,
            dicom_router_webhook_logs.created_at,
            dicom_router_webhook_logs.updated_at,
            dicom_router_webhook_logs.status,
            dicom_router_webhook_logs.stage,
            dicom_router_webhook_logs.message,
            dicom_router_webhook_logs.accession_number,
            dicom_router_webhook_logs.noorder,
            dicom_router_webhook_logs.imaging_study_id,
            dicom_router_webhook_logs.study_instance_uid,
            dicom_router_webhook_logs.error_code,
            dicom_router_webhook_logs.error_message,
            dicom_router_webhook_logs.delivery_count,
            ifnull(permintaan_radiologi.no_rawat, '-') no_rawat,
            ifnull(reg_periksa.no_rkm_medis, '-') no_rkm_medis,
            ifnull(pasien.nm_pasien, '-') nm_pasien,
            ifnull(dokter.nm_dokter, '-') dokter_perujuk
            SQL;

        $this->addSearchConditions([
            'permintaan_radiologi.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'dokter.nm_dokter',
        ]);

        $this->addRawColumns([
            'no_rawat'       => DB::raw("ifnull(permintaan_radiologi.no_rawat, '-')"),
            'no_rkm_medis'   => DB::raw("ifnull(reg_periksa.no_rkm_medis, '-')"),
            'nm_pasien'      => DB::raw("ifnull(pasien.nm_pasien, '-')"),
            'dokter_perujuk' => DB::raw("ifnull(dokter.nm_dokter, '-')"),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin(sprintf('%s.permintaan_radiologi as permintaan_radiologi', $sik), 'dicom_router_webhook_logs.noorder', '=', 'permintaan_radiologi.noorder')
            ->leftJoin(sprintf('%s.reg_periksa as reg_periksa', $sik), 'permintaan_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin(sprintf('%s.pasien as pasien', $sik), 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin(sprintf('%s.dokter as dokter', $sik), 'permintaan_radiologi.dokter_perujuk', '=', 'dokter.kd_dokter')
            ->whereBetween('dicom_router_webhook_logs.created_at', [$tglAwal.' 00:00:00', $tglAkhir.' 23:59:59.999999'])
            ->when($stage !== 'semua', fn (Builder $query): Builder => $query->where('dicom_router_webhook_logs.stage', $stage))
            ->when($hasil !== 'semua', fn (Builder $query): Builder => $query->where('dicom_router_webhook_logs.status', $hasil === 'berhasil'))
            ->orderByDesc('dicom_router_webhook_logs.created_at');
    }

    public function getStageLabelAttribute(): string
    {
        return static::STAGES[$this->stage] ?? (string) $this->stage;
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    protected static function sidikJari(array $atribut): string
    {
        return sha1((string) json_encode(Arr::except($atribut, 'payload')));
    }

    /**
     * Router membuang key bernilai null dari data, dan nilai yang tersisa belum
     * tentu string, jadi setiap field diperlakukan sebagai opsional. Nilainya
     * dipotong sepanjang kolom karena kedua koneksi berjalan dalam strict mode
     * dan payload yang kepanjangan lebih baik terpotong daripada gagal masuk.
     *
     * @param  mixed  $value
     */
    protected static function asString($value, ?int $panjang = null): ?string
    {
        if (is_null($value) || is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $panjang ? mb_substr($value, 0, $panjang) : $value;
    }
}
