<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Bangsal;
use App\Models\Keuangan\JenisPerawatanRanap;
use App\Models\Keuangan\KategoriPerawatan;
use App\Models\RekamMedis\Penjamin;
use App\Notifications\ImportTarifRanapNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemNotFoundException;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportTarifRanapJob implements ShouldQueue
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
     * @param array{
     * fileImport: \Livewire\TemporaryUploadedFile,
     * userId: string,
     * } $params
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
     *
     * @return void
     */
    public function handle()
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
                    'kd_kategori',
                    'material',
                    'bhp',
                    'tarif_tindakandr',
                    'tarif_tindakanpr',
                    'kso',
                    'menejemen',
                    'total_byrdr',
                    'total_byrpr',
                    'total_byrdrpr',
                    'kd_pj',
                    'kd_bangsal',
                    'kelas',
                ];

                $reader = SimpleExcelReader::create(storage_path('app/'.$this->fileImport));

                $headers = $reader->getHeaders();

                $missing = array_diff($requiredHeaders, $headers);

                if (! empty($missing)) {
                    throw new RuntimeException('Header file import tidak sesuai. Header yang hilang: '.implode(', ', $missing));
                }

                $kategoriMap = KategoriPerawatan::query()->pluck('kd_kategori')->flip();
                $penjaminMap = Penjamin::query()->pluck('kd_pj')->flip();
                $bangsalMap = Bangsal::query()->pluck('kd_bangsal')->flip();

                $rows = $reader->getRows();

                foreach ($rows as $index => $row) {

                    $line = $index + 2;

                    if (! $kategoriMap->has($row['kd_kategori'])) {
                        throw new RuntimeException("Kategori dengan kode {$row['kd_kategori']} tidak ditemukan.");
                    }

                    if (! $penjaminMap->has($row['kd_pj'])) {
                        throw new RuntimeException("Penjamin dengan kode {$row['kd_pj']} tidak ditemukan.");
                    }

                    if (! $bangsalMap->has($row['kd_bangsal'])) {
                        throw new RuntimeException("Bangsal dengan kode {$row['kd_bangsal']} tidak ditemukan.");
                    }

                    $subtotal = (
                        (float) $row['material'] +
                        (float) $row['bhp'] +
                        (float) $row['tarif_tindakandr'] +
                        (float) $row['tarif_tindakanpr'] +
                        (float) $row['kso'] +
                        (float) $row['menejemen']
                    );

                    if (
                        $subtotal < (float) $row['total_byrdr'] ||
                        $subtotal < (float) $row['total_byrpr'] ||
                        $subtotal < (float) $row['total_byrdrpr']
                    ) {
                        throw new RuntimeException("Baris {$line}: Total bayar tidak sesuai dengan rincian tarif.");
                    }

                    JenisPerawatanRanap::updateOrCreate(
                        ['kd_jenis_prw' => $row['kd_jenis_prw']],
                        [
                            'nm_perawatan'      => $row['nm_perawatan'],
                            'kd_kategori'       => $row['kd_kategori'],
                            'material'          => $row['material'],
                            'bhp'               => $row['bhp'],
                            'tarif_tindakandr'  => $row['tarif_tindakandr'],
                            'tarif_tindakanpr'  => $row['tarif_tindakanpr'],
                            'kso'               => $row['kso'],
                            'menejemen'         => $row['menejemen'],
                            'total_byrdr'       => $row['total_byrdr'],
                            'total_byrpr'       => $row['total_byrpr'],
                            'total_byrdrpr'     => $row['total_byrdrpr'],
                            'kd_pj'             => $row['kd_pj'],
                            'kd_bangsal'        => $row['kd_bangsal'],
                            'status'            => '1',
                            'kelas'             => $row['kelas'],
                        ]
                    );

                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::send($user,
                new ImportTarifRanapNotification($user, 'Import tarif rawat inap berhasil', 'success')
            );
        } catch (RuntimeException $e) {
            Notification::send($user,
                new ImportTarifRanapNotification($user, $e->getMessage(), 'error')
            );

            report($e);
            throw $e;
        } catch (Throwable $e) {
            Notification::send($user,
                new ImportTarifRanapNotification($user, 'Terjadi kesalahan saat mengimpor tarif rawat inap.', 'error')
            );

            report($e);
            throw $e;
        }
    }
}
