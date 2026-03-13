<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\PaketOperasi;
use App\Models\RekamMedis\Penjamin;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemNotFoundException;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportTarifOperasiJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $fileImport;

    private string $userId;

    /**
     * Create a new job instance.
     *
     * @param  array{
     * fileImport: \Livewire\TemporaryUploadedFile,
     * userId: string,
     * }  $params
     */
    public function __construct(array $params)
    {
        $path = $params['fileImport']->store('temp');

        if ($path === false) {
            throw new FilesystemNotFoundException('Temporary file not available');
        }

        $this->fileImport = $path;
        $this->userId = $params['userId'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->fileImport) {
            throw new FilesystemNotFoundException('File import not found');
        }

        $this->proceed();

        Storage::delete($this->fileImport);
    }

    protected function proceed(): void
    {
        $user = User::findByNRP($this->userId);

        try {
            DB::connection('mysql_sik')->transaction(function () {

                tracker_start('mysql_sik');

                $requiredHeaders = [
                    'kode_paket',
                    'nm_perawatan',
                    'kategori',
                    'operator1',
                    'operator2',
                    'operator3',
                    'asisten_operator1',
                    'asisten_operator2',
                    'asisten_operator3',
                    'instrumen',
                    'dokter_anestesi',
                    'asisten_anestesi',
                    'asisten_anestesi2',
                    'dokter_anak',
                    'perawaat_resusitas',
                    'bidan',
                    'bidan2',
                    'bidan3',
                    'perawat_luar',
                    'alat',
                    'sewa_ok',
                    'akomodasi',
                    'bagian_rs',
                    'omloop',
                    'omloop2',
                    'omloop3',
                    'omloop4',
                    'omloop5',
                    'sarpras',
                    'dokter_pjanak',
                    'dokter_umum',
                    'kd_pj',
                    'kelas',
                ];

                $reader = SimpleExcelReader::create(storage_path('app/'.$this->fileImport));

                $headers = $reader->getHeaders();

                $missing = array_diff($requiredHeaders, $headers);

                if (! empty($missing)) {
                    throw new RuntimeException('Header file import tidak sesuai. Header yang hilang: '.implode(', ', $missing));
                }

                $penjaminMap = Penjamin::query()->pluck('kd_pj')->flip();

                $rows = $reader->getRows();

                foreach ($rows as $index => $row) {

                    $line = $index + 2;

                    if (! $penjaminMap->has($row['kd_pj'])) {
                        throw new RuntimeException("Baris {$line}: kd_pj tidak ditemukan");
                    }

                    PaketOperasi::query()->updateOrCreate(
                        ['kode_paket' => $row['kode_paket']],
                        [
                            'nm_perawatan'       => $row['nm_perawatan'],
                            'kategori'           => $row['kategori'],
                            'operator1'          => $row['operator1'],
                            'operator2'          => $row['operator2'],
                            'operator3'          => $row['operator3'],
                            'asisten_operator1'  => $row['asisten_operator1'],
                            'asisten_operator2'  => $row['asisten_operator2'],
                            'asisten_operator3'  => $row['asisten_operator3'],
                            'instrumen'          => $row['instrumen'],
                            'dokter_anestesi'    => $row['dokter_anestesi'],
                            'asisten_anestesi'   => $row['asisten_anestesi'],
                            'asisten_anestesi2'  => $row['asisten_anestesi2'],
                            'dokter_anak'        => $row['dokter_anak'],
                            'perawaat_resusitas' => $row['perawaat_resusitas'],
                            'bidan'              => $row['bidan'],
                            'bidan2'             => $row['bidan2'],
                            'bidan3'             => $row['bidan3'],
                            'perawat_luar'       => $row['perawat_luar'],
                            'alat'               => $row['alat'],
                            'sewa_ok'            => $row['sewa_ok'],
                            'akomodasi'          => $row['akomodasi'],
                            'bagian_rs'          => $row['bagian_rs'],
                            'omloop'             => $row['omloop'],
                            'omloop2'            => $row['omloop2'],
                            'omloop3'            => $row['omloop3'],
                            'omloop4'            => $row['omloop4'],
                            'omloop5'            => $row['omloop5'],
                            'sarpras'            => $row['sarpras'],
                            'dokter_pjanak'      => $row['dokter_pjanak'],
                            'dokter_umum'        => $row['dokter_umum'],
                            'kd_pj'              => $row['kd_pj'],
                            'kelas'              => $row['kelas'],
                            'status'             => '1',
                        ]);
                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::make()
                ->message('Import tarif operasi berhasil')
                ->success()
                ->send($user);

        } catch (RuntimeException $e) {
            Notification::make()
                ->message($e->getMessage())
                ->danger()
                ->send($user);

            report($e);
            throw $e;
        } catch (Throwable $e) {
            Notification::make()
                ->message('Terjadi kesalahan saat mengimpor tarif operasi.')
                ->danger()
                ->send($user);

            report($e);
            throw $e;
        }
    }
}
