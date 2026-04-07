<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\JenisPerawatanRadiologi;
use App\Models\RekamMedis\Penjamin;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemNotFoundException;
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

                $headerMapping = [
                    'Kode Periksa'          => 'kd_jenis_prw',
                    'Nama Pemeriksaan'      => 'nm_perawatan',
                    'Jasa Sarana'           => 'bagian_rs',
                    'Paket BHP'             => 'bhp',
                    'Jasa Medis Perujuk'    => 'tarif_perujuk',
                    'Jasa Medis Dokter'     => 'tarif_tindakan_dokter',
                    'Jasa Medis Petugas'    => 'tarif_tindakan_petugas',
                    'KSO'                   => 'kso',
                    'Menejemen'             => 'menejemen',
                    'Total Tarif'           => 'total_byr',
                    'Jenis Bayar'           => 'kd_pj',
                    'Kelas'                 => 'kelas',
                ];

                $requiredHeaders = array_keys($headerMapping);

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

                    // Map UI headers to database keys
                    $data = [];
                    foreach ($headerMapping as $uiHeader => $dbKey) {
                        $data[$dbKey] = $row[$uiHeader] ?? null;
                    }

                    if (! $penjaminMap->has($data['kd_pj'])) {
                        throw new RuntimeException("Baris {$line}: Jenis Bayar '{$data['kd_pj']}' tidak ditemukan");
                    }

                    $data['bagian_rs'] = parse_numeric($data['bagian_rs'] ?? 0);
                    $data['bhp'] = parse_numeric($data['bhp'] ?? 0);
                    $data['tarif_perujuk'] = parse_numeric($data['tarif_perujuk'] ?? 0);
                    $data['tarif_tindakan_dokter'] = parse_numeric($data['tarif_tindakan_dokter'] ?? 0);
                    $data['tarif_tindakan_petugas'] = parse_numeric($data['tarif_tindakan_petugas'] ?? 0);
                    $data['kso'] = parse_numeric($data['kso'] ?? 0);
                    $data['menejemen'] = parse_numeric($data['menejemen'] ?? 0);
                    $data['total_byr'] = parse_numeric($data['total_byr'] ?? 0);

                    $subtotal = (
                        (float) $data['bagian_rs'] +
                        (float) $data['bhp'] +
                        (float) $data['tarif_perujuk'] +
                        (float) $data['tarif_tindakan_dokter'] +
                        (float) $data['tarif_tindakan_petugas'] +
                        (float) $data['kso'] +
                        (float) $data['menejemen']
                    );

                    if ($subtotal != (float) $data['total_byr']) {
                        throw new RuntimeException("Baris {$line}: Total biaya tidak sesuai dengan rincian tarif.");
                    }

                    try {
                        JenisPerawatanRadiologi::query()->updateOrCreate(
                            ['kd_jenis_prw' => $data['kd_jenis_prw']],
                            [
                                'nm_perawatan'           => $data['nm_perawatan'],
                                'bagian_rs'              => $data['bagian_rs'],
                                'bhp'                    => $data['bhp'],
                                'tarif_perujuk'          => $data['tarif_perujuk'],
                                'tarif_tindakan_dokter'  => $data['tarif_tindakan_dokter'],
                                'tarif_tindakan_petugas' => $data['tarif_tindakan_petugas'],
                                'kso'                    => $data['kso'],
                                'menejemen'              => $data['menejemen'],
                                'total_byr'              => $data['total_byr'],
                                'kd_pj'                  => $data['kd_pj'] ?? '-',
                                'kelas'                  => $data['kelas'] ?? '-',
                                'status'                 => '1',
                            ]);
                    } catch (QueryException $e) {
                        throw new RuntimeException("Baris {$line}: Gagal menyimpan data ke database. Pastikan format data sudah benar.");
                    }
                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::make()
                ->message('Import tarif radiologi berhasil')
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
                ->message('Terjadi kesalahan saat mengimpor tarif radiologi.')
                ->danger()
                ->send($user);

            report($e);
            throw $e;
        }
    }
}
