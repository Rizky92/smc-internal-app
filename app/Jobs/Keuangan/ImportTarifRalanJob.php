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

                $headerMapping = [
                    'Kode Tindakan'         => 'kd_jenis_prw',
                    'Nama Tnd/Prw/Tagihan'  => 'nama_tarif',
                    'Kategori'              => 'kd_kategori',
                    'Jasa Sarana'           => 'material',
                    'BHP/Paket Obat'        => 'bhp',
                    'Jasa Medis Dr'         => 'tarif_tindakandr',
                    'Jasa Medis Pr'         => 'tarif_tindakanpr',
                    'KSO'                   => 'kso',
                    'Menejemen'             => 'menejemen',
                    'Ttl Biaya Dr'          => 'total_byrdr',
                    'Ttl Biaya Pr'          => 'total_byrpr',
                    'Ttl Biaya Dr & Pr'     => 'total_byrdrpr',
                    'Jenis Bayar'           => 'kd_pj',
                    'Poli'                  => 'kd_poli',
                ];

                $requiredHeaders = array_keys($headerMapping);

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

                    // Map UI headers to database keys
                    $data = [];
                    foreach ($headerMapping as $uiHeader => $dbKey) {
                        $data[$dbKey] = $row[$uiHeader] ?? null;
                    }

                    if (! $kategoriMap->has($data['kd_kategori'])) {
                        throw new RuntimeException("Baris {$line}: Kategori '{$data['kd_kategori']}' tidak ditemukan");
                    }

                    if (! $penjaminMap->has($data['kd_pj'])) {
                        throw new RuntimeException("Baris {$line}: Jenis Bayar '{$data['kd_pj']}' tidak ditemukan");
                    }

                    if (! $poliMap->has($data['kd_poli'])) {
                        throw new RuntimeException("Baris {$line}: Poli '{$data['kd_poli']}' tidak ditemukan");
                    }

                    $data['material'] = parse_numeric($data['material'] ?? 0);
                    $data['bhp'] = parse_numeric($data['bhp'] ?? 0);
                    $data['tarif_tindakandr'] = parse_numeric($data['tarif_tindakandr'] ?? 0);
                    $data['tarif_tindakanpr'] = parse_numeric($data['tarif_tindakanpr'] ?? 0);
                    $data['kso'] = parse_numeric($data['kso'] ?? 0);
                    $data['menejemen'] = parse_numeric($data['menejemen'] ?? 0);
                    $data['total_byrdr'] = parse_numeric($data['total_byrdr'] ?? 0);
                    $data['total_byrpr'] = parse_numeric($data['total_byrpr'] ?? 0);
                    $data['total_byrdrpr'] = parse_numeric($data['total_byrdrpr'] ?? 0);

                    $calcTotalDr = $data['material'] + $data['bhp'] + $data['tarif_tindakandr'] + $data['kso'] + $data['menejemen'];
                    $calcTotalPr = $data['material'] + $data['bhp'] + $data['tarif_tindakanpr'] + $data['kso'] + $data['menejemen'];
                    $calcTotalDrPr = $data['material'] + $data['bhp'] + $data['tarif_tindakandr'] + $data['tarif_tindakanpr'] + $data['kso'] + $data['menejemen'];

                    if (
                        $calcTotalDr != (float) $data['total_byrdr'] &&
                        $calcTotalPr != (float) $data['total_byrpr'] &&
                        $calcTotalDrPr != (float) $data['total_byrdrpr']
                    ) {
                        throw new RuntimeException("Baris {$line}: Tidak ada total biaya yang sesuai dengan rincian tarif (Minimal salah satu Total DR, PR, atau DR&PR harus sesuai).");
                    }

                    try {

                        JenisPerawatan::query()->updateOrCreate(
                            ['kd_jenis_prw' => $data['kd_jenis_prw']],
                            [
                                'nm_perawatan'      => $data['nama_tarif'],
                                'material'          => $data['material'],
                                'bhp'               => $data['bhp'],
                                'tarif_tindakandr'  => $data['tarif_tindakandr'],
                                'tarif_tindakanpr'  => $data['tarif_tindakanpr'],
                                'kso'               => $data['kso'],
                                'menejemen'         => $data['menejemen'],
                                'total_byrdr'       => $data['total_byrdr'],
                                'total_byrpr'       => $data['total_byrpr'],
                                'total_byrdrpr'     => $data['total_byrdrpr'],
                                'kd_kategori'       => $data['kd_kategori'],
                                'kd_pj'             => $data['kd_pj'],
                                'kd_poli'           => $data['kd_poli'],
                                'status'            => '1',
                            ]);
                    } catch (QueryException $e) {
                        throw new RuntimeException("Baris {$line}: Gagal menyimpan data ke database. Pastikan format data sudah benar.");
                    }
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
