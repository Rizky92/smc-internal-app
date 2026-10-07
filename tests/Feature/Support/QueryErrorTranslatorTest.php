<?php

namespace Tests\Feature\Support;

use App\Support\QueryErrorTranslator;
use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

class QueryErrorTranslatorTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql_sik'];

    private const HEADERS = [
        'Kode Paket'   => 'kode_paket',
        'Nama Operasi' => 'nm_perawatan',
        'Kategori'     => 'kategori',
        'Operator 1'   => 'operator1',
        'Jenis Bayar'  => 'kd_pj',
        'Kelas'        => 'kelas',
    ];

    private const KELAS = '-, Rawat Jalan, Kelas 1, Kelas 2, Kelas 3, Kelas Utama, Kelas VIP, Kelas VVIP';

    private function db(): ConnectionInterface
    {
        return DB::connection('mysql_sik');
    }

    private function translator(): QueryErrorTranslator
    {
        return new QueryErrorTranslator($this->db());
    }

    private function capture(Closure $query): QueryException
    {
        try {
            $query();
        } catch (QueryException $e) {
            return $e;
        }

        $this->fail('Query seharusnya gagal');
    }

    /**
     * Insert into paket_operasi with every NOT NULL column filled, then override.
     *
     * @param  array<string, mixed>  $override
     */
    private function insertPaket(array $override): QueryException
    {
        $row = ['kode_paket' => 'ZTQE1', 'nm_perawatan' => 'Op', 'kategori' => 'Operasi', 'kd_pj' => '-', 'status' => '1', 'kelas' => '-'];

        foreach (['operator1', 'operator2', 'operator3', 'asisten_operator2', 'dokter_anak', 'perawaat_resusitas', 'dokter_anestesi', 'asisten_anestesi', 'bidan', 'perawat_luar', 'sewa_ok', 'alat', 'bagian_rs', 'omloop'] as $col) {
            $row[$col] = 0;
        }

        return $this->capture(fn () => $this->db()->table('paket_operasi')->insert(array_merge($row, $override)));
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function translatePaket(QueryException $e, array $raw): string
    {
        return $this->translator()->translate($e, 'paket_operasi', 228, self::HEADERS, $raw);
    }

    public function test_1265_enum_value_lists_all_choices(): void
    {
        $e = $this->insertPaket(['kelas' => 'VIP']);

        $this->assertSame(
            "Baris 228: Kelas 'VIP' tidak valid. Pilihan: ".self::KELAS.' (kode 1265)',
            $this->translatePaket($e, ['kelas' => 'VIP'])
        );
    }

    public function test_1265_blank_enum_is_required(): void
    {
        $e = $this->insertPaket(['kelas' => '']);

        $this->assertSame(
            'Baris 228: Kelas wajib diisi. Pilihan: '.self::KELAS.' (kode 1265)',
            $this->translatePaket($e, ['kelas' => " \u{00A0}"])
        );
    }

    public function test_message_shows_raw_value_typed_by_user(): void
    {
        $e = $this->insertPaket(['kategori' => 'operasi besar']);

        $this->assertSame(
            "Baris 228: Kategori 'Operasi Besar' tidak valid. Pilihan: Kebidanan, Operasi (kode 1265)",
            $this->translatePaket($e, ['kategori' => 'Operasi Besar'])
        );
    }

    public function test_1406_reports_max_length(): void
    {
        $e = $this->insertPaket(['kode_paket' => 'OPR-2026-0000228']);

        $this->assertSame(
            "Baris 228: Kode Paket 'OPR-2026-0000228' melebihi 15 karakter (kode 1406)",
            $this->translatePaket($e, ['kode_paket' => 'OPR-2026-0000228'])
        );
    }

    public function test_long_values_are_cut_to_50_characters(): void
    {
        $nama = str_repeat('a', 100);
        $e = $this->insertPaket(['nm_perawatan' => $nama]);

        $this->assertSame(
            "Baris 228: Nama Operasi '".str_repeat('a', 50)."...' melebihi 80 karakter (kode 1406)",
            $this->translatePaket($e, ['nm_perawatan' => $nama])
        );
    }

    public function test_1048_is_required(): void
    {
        $e = $this->insertPaket(['nm_perawatan' => null]);

        $this->assertSame('Baris 228: Nama Operasi wajib diisi (kode 1048)', $this->translatePaket($e, ['nm_perawatan' => null]));
    }

    public function test_1366_must_be_a_number(): void
    {
        $e = $this->insertPaket(['operator1' => 'abc']);

        $this->assertSame("Baris 228: Operator 1 'abc' harus berupa angka (kode 1366)", $this->translatePaket($e, ['operator1' => 'abc']));
    }

    public function test_1452_not_found(): void
    {
        $e = $this->insertPaket(['kd_pj' => 'ZZZ']);

        $this->assertSame("Baris 228: Jenis Bayar 'ZZZ' tidak ditemukan (kode 1452)", $this->translatePaket($e, ['kd_pj' => 'ZZZ']));
    }

    public function test_unmapped_column_uses_raw_column_name(): void
    {
        $e = $this->insertPaket(['status' => '9']);

        $this->assertSame('Baris 228: status tidak valid. Pilihan: 0, 1 (kode 1265)', $this->translatePaket($e, []));
    }

    public function test_1062_single_column_unique_key(): void
    {
        $this->db()->table('bahasa_pasien')->insert(['nama_bahasa' => 'ZTBHS']);
        $e = $this->capture(fn () => $this->db()->table('bahasa_pasien')->insert(['nama_bahasa' => 'ZTBHS']));

        $this->assertSame(
            "Baris 5: Bahasa 'ZTBHS' sudah ada (kode 1062)",
            $this->translator()->translate($e, 'bahasa_pasien', 5, ['Bahasa' => 'nama_bahasa'], ['nama_bahasa' => 'ZTBHS'])
        );
    }

    public function test_1062_composite_unique_key_lists_every_header(): void
    {
        $row = ['dep_id' => '-', 'shift' => 'Pagi10', 'jam_masuk' => '07:00:00', 'jam_pulang' => '14:00:00'];
        $this->db()->table('jam_jaga')->insert($row);
        $e = $this->capture(fn () => $this->db()->table('jam_jaga')->insert($row));

        $this->assertSame(
            "Baris 5: kombinasi Departemen, Shift ('-', 'Pagi10') sudah ada (kode 1062)",
            $this->translator()->translate($e, 'jam_jaga', 5, ['Departemen' => 'dep_id', 'Shift' => 'shift'], ['dep_id' => '-', 'shift' => 'Pagi10'])
        );
    }

    public function test_1062_falls_back_to_duplicate_value_when_key_lookup_fails(): void
    {
        $this->db()->table('bahasa_pasien')->insert(['nama_bahasa' => 'ZTBHS']);
        $e = $this->capture(fn () => $this->db()->table('bahasa_pasien')->insert(['nama_bahasa' => 'ZTBHS']));

        $this->assertSame(
            "Baris 5: data 'ZTBHS' sudah ada (duplikat) (kode 1062)",
            $this->translator()->translate($e, 'tabel_tidak_ada', 5, ['Bahasa' => 'nama_bahasa'], ['nama_bahasa' => 'ZTBHS'])
        );
    }

    public function test_unknown_code_falls_back_to_retry_message(): void
    {
        $pdo = new PDOException('Deadlock found when trying to get lock');
        $pdo->errorInfo = ['40001', 1213, 'Deadlock found when trying to get lock; try restarting transaction'];

        $this->assertSame(
            'Baris 228: Gagal menyimpan data ke database. Coba ulangi import; jika masih gagal, hubungi tim IT. (kode 1213)',
            $this->translatePaket(new QueryException('insert ...', [], $pdo), [])
        );
    }

    public function test_mysql8_message_formats_are_parsed(): void
    {
        $double = new PDOException('x');
        $double->errorInfo = ['HY000', 1366, "Incorrect double value: 'abc' for column 'operator1' at row 1"];

        $dup = new PDOException('x');
        $dup->errorInfo = ['23000', 1062, "Duplicate entry 'ZTQE1' for key 'paket_operasi.PRIMARY'"];

        $this->assertSame("Baris 228: Operator 1 'abc' harus berupa angka (kode 1366)", $this->translatePaket(new QueryException('', [], $double), ['operator1' => 'abc']));
        $this->assertSame("Baris 228: Kode Paket 'ZTQE1' sudah ada (kode 1062)", $this->translatePaket(new QueryException('', [], $dup), ['kode_paket' => 'ZTQE1']));
    }
}
