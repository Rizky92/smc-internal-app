<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\JenisPerawatan;
use App\Models\Keuangan\KategoriPerawatan;
use App\Models\Perawatan\Poliklinik;
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

class ImportTarifRalanJob implements ShouldQueue
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
                    'nama_tarif',
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
                    'kd_poli',
                ];

                $reader = SimpleExcelReader::create(storage_path('app/'.$this->fileImport));

                $headers = $reader->getHeaders();

                $missing = array_diff($requiredHeaders, $headers);

                if (! empty($missing)) {
                    throw new RuntimeException('Header file import tidak sesuai. Header yang hilang: '.implode(', ', $missing));
                }

                $kategoriMap = KategoriPerawatan::query()->pluck('kd_kategori')->flip();
                $penjaminMap = Penjamin::query()->pluck('kd_pj')->flip();
                $poliMap = Poliklinik::query()->pluck('kd_poli')->flip();

                $rows = $reader->getRows();

                foreach ($rows as $index => $row) {

                    $line = $index + 2;

                    if (! $kategoriMap->has($row['kd_kategori'])) {
                        throw new RuntimeException("Baris {$line}: kd_kategori tidak ditemukan");
                    }

                    if (! $penjaminMap->has($row['kd_pj'])) {
                        throw new RuntimeException("Baris {$line}: kd_pj tidak ditemukan");
                    }

                    if (! $poliMap->has($row['kd_poli'])) {
                        throw new RuntimeException("Baris {$line}: kd_poli tidak ditemukan");
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
                        throw new RuntimeException("Baris {$line}: Total biaya tidak sesuai dengan rincian tarif.");
                    }

                    JenisPerawatan::query()->updateOrCreate(
                        ['kd_jenis_prw' => $row['kd_jenis_prw']],
                        [
                            'nm_perawatan'      => $row['nama_tarif'],
                            'material'          => $row['material'],
                            'bhp'               => $row['bhp'],
                            'tarif_tindakandr'  => $row['tarif_tindakandr'],
                            'tarif_tindakanpr'  => $row['tarif_tindakanpr'],
                            'kso'               => $row['kso'],
                            'menejemen'         => $row['menejemen'],
                            'total_byrdr'       => $row['total_byrdr'],
                            'total_byrpr'       => $row['total_byrpr'],
                            'total_byrdrpr'     => $row['total_byrdrpr'],
                            'kd_kategori'       => $row['kd_kategori'],
                            'kd_pj'             => $row['kd_pj'],
                            'kd_poli'           => $row['kd_poli'],
                            'status'            => '1',
                        ]);
                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::make()
                ->message('Import tarif rawat jalan berhasil')
                ->success()
                ->send($user);

        } catch (RuntimeException $e) {
            Notification::make()->message($e->getMessage())->danger()->send($user);

            report($e);
            throw $e;
        } catch (Throwable $e) {
            Notification::make()
                ->message('Terjadi kesalahan saat mengimpor tarif rawat jalan.')
                ->danger()
                ->send($user);

            report($e);
            throw $e;
        }
    }
}
