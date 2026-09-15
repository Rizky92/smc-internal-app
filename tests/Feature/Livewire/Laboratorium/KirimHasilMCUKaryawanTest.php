<?php

namespace Tests\Feature\Livewire\Laboratorium;

use App\Livewire\Pages\Laboratorium\KirimHasilMCUKaryawan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the employee-list and MCU-visit-list this page's "Kirim email"
 * action would send results for.
 *
 * sendEmail() itself is not exercised here - it is dd($this->checkedPasien),
 * a debug dump-and-die left in place of the actual mail-sending logic, wired
 * up to a real, enabled "Kirim email" button in the Blade view. Calling it
 * would halt the request with a var-dump instead of a Livewire response, so
 * there is nothing meaningful to assert about it (Mail::fake() never comes
 * into play, since no Mail::send()/Mailable is ever dispatched). This is
 * unfinished production code, not a test gap - flagged for a decision
 * (finish it or pull the button) rather than faked with hollow coverage or
 * fixed unilaterally, matching how ModalHakAksesBaru was handled earlier in
 * this pass.
 *
 * What is real and covered: both patient lists narrow correctly to a
 * selected employer (perusahaan), which is the actual pre-filtering step
 * before anyone would click that button.
 */
class KirimHasilMCUKaryawanTest extends TestCase
{
    private const PERMISSION = 'lab.hasil-mcu-karyawan.read';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('reg_periksa')->where('no_rawat', 'like', 'UJI%')->delete();

        parent::tearDown();

        // pasien (deleted by parent::tearDown()) references perusahaan_pasien,
        // so this can only be dropped once pasien is gone.
        $sik->table('perusahaan_pasien')->where('kode_perusahaan', 'like', 'UJI%')->delete();
    }

    private function karyawan(string $nomor, string $kodePerusahaan): void
    {
        $sik = DB::connection('mysql_sik');
        $noRekamMedis = 'UJI-RM'.$nomor;
        $noRawat = 'UJI/'.$nomor;

        if (! $sik->table('perusahaan_pasien')->where('kode_perusahaan', $kodePerusahaan)->exists()) {
            $sik->table('perusahaan_pasien')->insert([
                'kode_perusahaan' => $kodePerusahaan, 'nama_perusahaan' => 'PT '.$kodePerusahaan,
            ]);
        }

        $this->createPasien($noRekamMedis, 'Karyawan '.$nomor);
        $sik->table('pasien')->where('no_rkm_medis', $noRekamMedis)->update(['perusahaan_pasien' => $kodePerusahaan]);

        $this->createRegistrasi($noRawat, $noRekamMedis, '2026-03-05');
        $sik->table('reg_periksa')->where('no_rawat', $noRawat)->update(['kd_poli' => 'U0036']);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(KirimHasilMCUKaryawan::class);
    }

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $this->report()->assertOk();
    }

    /**
     * @test
     */
    public function narrows_the_employee_list_to_the_selected_company(): void
    {
        $this->karyawan('01', 'UJIPT1');
        $this->karyawan('02', 'UJIPT2');

        $test = $this->report()->set('perusahaan', 'UJIPT1');

        $this->assertSame(
            ['Karyawan 01'],
            $test->instance()->dataPasien->pluck('nm_pasien')->all()
        );
    }

    /**
     * @test
     *
     * The MCU-visit list is a different query (RegistrasiPasien, filtered to
     * kd_poli U0036) but narrows through the same perusahaan filter, via the
     * pasien.perusahaan relation rather than a column of its own.
     */
    public function narrows_the_mcu_visit_list_to_the_selected_company(): void
    {
        $this->karyawan('03', 'UJIPT3');
        $this->karyawan('04', 'UJIPT4');

        $test = $this->report()->set('perusahaan', 'UJIPT3');

        $this->assertSame(
            ['UJI/03'],
            $test->instance()->dataPasienPoliMCU->pluck('no_rawat')->all()
        );
    }
}
