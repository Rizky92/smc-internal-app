<?php

namespace Tests\Feature\Informasi;

use App\Livewire\Pages\Informasi\AntreanFarmasi\NomorDipanggil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire;
use Tests\TestCase;

class AntreanFarmasiNomorDipanggilTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql_sik'];

    protected function setUp(): void
    {
        parent::setUp();

        // Kosongkan antrean yang ada agar tidak mengganggu test; dikembalikan oleh rollback DatabaseTransactions.
        DB::connection('mysql_sik')->table('antriloketfarmasi_smc')->delete();
    }

    private function antrean(string $nomor, string $tanggal, ?string $jamPanggil): void
    {
        DB::connection('mysql_sik')->table('antriloketfarmasi_smc')->insert([
            'nomor'       => $nomor,
            'tanggal'     => $tanggal,
            'jam'         => '07:00:00',
            'jam_panggil' => $jamPanggil,
            'no_resep'    => null,
        ]);
    }

    public function test_halaman_antrean_farmasi_memiliki_component_nomor_dipanggil()
    {
        $this->get(route('antrean-farmasi'))
            ->assertOk()
            ->assertSeeLivewire(NomorDipanggil::class);
    }

    public function test_menampilkan_nomor_terakhir_dipanggil_hari_ini()
    {
        $hariIni = now()->toDateString();

        $this->antrean('0001', $hariIni, '08:00:00');
        $this->antrean('0002', $hariIni, '08:15:00');
        $this->antrean('0003', $hariIni, '08:05:00');

        Livewire::test(NomorDipanggil::class)
            ->assertSeeInOrder(['Nomor Antrean Dipanggil', '0002', 'Dipanggil pukul 08:15'])
            ->assertDontSee('Belum ada panggilan');
    }

    public function test_nomor_yang_dipanggil_ulang_menjadi_yang_terakhir()
    {
        $hariIni = now()->toDateString();

        $this->antrean('0005', $hariIni, '09:30:00');
        $this->antrean('0009', $hariIni, '09:10:00');

        Livewire::test(NomorDipanggil::class)
            ->assertSee('0005')
            ->assertSee('Dipanggil pukul 09:30');
    }

    public function test_panggilan_pada_detik_yang_sama_menampilkan_nomor_terbesar()
    {
        $hariIni = now()->toDateString();

        // Inserted high-first so an unordered tie would tend to return 0012.
        $this->antrean('0012', $hariIni, '09:45:00');
        $this->antrean('0013', $hariIni, '09:45:00');

        Livewire::test(NomorDipanggil::class)
            ->assertSee('0013')
            ->assertDontSee('0012');
    }

    public function test_antrean_yang_belum_dipanggil_diabaikan()
    {
        $hariIni = now()->toDateString();

        $this->antrean('0004', $hariIni, '10:00:00');
        $this->antrean('0008', $hariIni, null);

        Livewire::test(NomorDipanggil::class)
            ->assertSee('0004')
            ->assertDontSee('0008');
    }

    public function test_panggilan_kemarin_tidak_ditampilkan()
    {
        $this->antrean('0042', now()->subDay()->toDateString(), '15:00:00');

        Livewire::test(NomorDipanggil::class)
            ->assertDontSee('0042')
            ->assertSeeInOrder(['Nomor Antrean Dipanggil', '–', 'Belum ada panggilan']);
    }

    public function test_tanpa_antrean_menampilkan_belum_ada_panggilan()
    {
        Livewire::test(NomorDipanggil::class)
            ->assertSeeInOrder(['Nomor Antrean Dipanggil', '–', 'Belum ada panggilan']);
    }

    public function test_nomor_ditampilkan_apa_adanya_dengan_nol_di_depan()
    {
        $this->antrean('0007', now()->toDateString(), '11:00:00');

        Livewire::test(NomorDipanggil::class)
            ->assertSee('0007');
    }
}
