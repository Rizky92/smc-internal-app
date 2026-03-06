<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\JenisPerawatanRadiologi;
use App\Models\RekamMedis\Penjamin;
use App\Notifications\ImportTarifRadiologiNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemNotFoundException;
use Livewire\TemporaryUploadedFile;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportTarifRadiologiJob implements ShouldQueue
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
                    'kd_jenis_prw',
                    'nm_perawatan',
                    'bagian_rs',
                    'bhp',
                    'tarif_perujuk',
                    'tarif_tindakan_dokter',
                    'tarif_tindakan_petugas',
                    'kso',
                    'menejemen',
                    'total_byr',
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

                    $subtotal = (
                        (float) $row['bagian_rs'] +
                        (float) $row['bhp'] +
                        (float) $row['tarif_perujuk'] +
                        (float) $row['tarif_tindakan_dokter'] +
                        (float) $row['tarif_tindakan_petugas'] +
                        (float) $row['kso'] +
                        (float) $row['menejemen']
                    );

                    if ($subtotal < (float) $row['total_byr']) {
                        throw new RuntimeException("Baris {$line}: Total biaya tidak sesuai dengan rincian tarif.");
                    }

                    JenisPerawatanRadiologi::query()->updateOrCreate(
                        ['kd_jenis_prw' => $row['kd_jenis_prw']],
                        [
                            'nm_perawatan'           => $row['nm_perawatan'],
                            'bagian_rs'              => $row['bagian_rs'],
                            'bhp'                    => $row['bhp'],
                            'tarif_perujuk'          => $row['tarif_perujuk'],
                            'tarif_tindakan_dokter'  => $row['tarif_tindakan_dokter'],
                            'tarif_tindakan_petugas' => $row['tarif_tindakan_petugas'],
                            'kso'                    => $row['kso'],
                            'menejemen'              => $row['menejemen'],
                            'total_byr'              => $row['total_byr'],
                            'kd_pj'                  => $row['kd_pj'],
                            'kelas'                  => $row['kelas'],
                            'status'                 => '1',
                        ]);
                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::send($user, new ImportTarifRadiologiNotification($user, 'Import tarif radiologi berhasil', 'success'));

        } catch (RuntimeException $e) {
            Notification::send($user,
                new ImportTarifRadiologiNotification($user, $e->getMessage(), 'error')
            );

            report($e);
            throw $e;
        } catch (Throwable $e) {

            Notification::send($user,
                new ImportTarifRadiologiNotification($user, 'Terjadi kesalahan saat mengimpor tarif radiologi.', 'error')
            );

            report($e);
            throw $e;
        }
    }
}
