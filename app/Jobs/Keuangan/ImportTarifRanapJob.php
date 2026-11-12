<?php

namespace App\Jobs\Keuangan;

use App\Exceptions\ImportTarifException;
use App\Jobs\Keuangan\Concerns\ImportsTarifRows;
use App\Models\Aplikasi\User;
use App\Models\Bangsal;
use App\Models\Keuangan\JenisPerawatanRanap;
use App\Models\Keuangan\KategoriPerawatan;
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
use Livewire\TemporaryUploadedFile;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportTarifRanapJob implements ShouldQueue
{
    use Dispatchable;
    use ImportsTarifRows;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $fileImport;

    private string $userId;

    /**
     * Create a new job instance.
     *
     * @param  array{
     * fileImport: TemporaryUploadedFile,
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

                $headerMapping = [
                    'Kode Tindakan'        => 'kd_jenis_prw',
                    'Nama Tnd/Prw/Tagihan' => 'nm_perawatan',
                    'Kategori'             => 'kd_kategori',
                    'Jasa Sarana'          => 'material',
                    'BHP/Paket Obat'       => 'bhp',
                    'Jasa Medis Dr'        => 'tarif_tindakandr',
                    'Jasa Medis Pr'        => 'tarif_tindakanpr',
                    'KSO'                  => 'kso',
                    'Menejemen'            => 'menejemen',
                    'Total Biaya Dr'       => 'total_byrdr',
                    'Total Biaya Pr'       => 'total_byrpr',
                    'Total Biaya Dr & Pr'  => 'total_byrdrpr',
                    'Jenis Bayar'          => 'kd_pj',
                    'Kamar'                => 'kd_bangsal',
                    'Kelas'                => 'kelas',
                ];

                $requiredHeaders = array_keys($headerMapping);

                $reader = SimpleExcelReader::create(storage_path('app/'.$this->fileImport));

                $headers = $reader->getHeaders();

                $missing = array_diff($requiredHeaders, $headers);

                if (! empty($missing)) {
                    throw new ImportTarifException('Header file import tidak sesuai. Header yang hilang: '.implode(', ', $missing));
                }

                $kategoriMap = KategoriPerawatan::query()->pluck('kd_kategori')->flip();
                $penjaminMap = Penjamin::query()->where('status', '1')->pluck('kd_pj')->flip();
                $bangsalMap = Bangsal::query()->pluck('kd_bangsal')->flip();

                foreach ($this->dataRows($reader) as $line => $row) {

                    // Map UI headers to database keys
                    $data = [];
                    foreach ($headerMapping as $uiHeader => $dbKey) {
                        $data[$dbKey] = $row[$uiHeader] ?? null;
                    }

                    $raw = $data;

                    if (! $kategoriMap->has($data['kd_kategori'])) {
                        throw new ImportTarifException("Baris {$line}: Kategori '{$data['kd_kategori']}' tidak ditemukan");
                    }

                    if (! $penjaminMap->has($data['kd_pj'])) {
                        throw new ImportTarifException("Baris {$line}: Jenis Bayar '{$data['kd_pj']}' tidak ditemukan");
                    }

                    if (! $bangsalMap->has($data['kd_bangsal'])) {
                        throw new ImportTarifException("Baris {$line}: Bangsal '{$data['kd_bangsal']}' tidak ditemukan");
                    }

                    $data = $this->parseAmounts($line, $data, $headerMapping, ['material', 'bhp', 'tarif_tindakandr', 'tarif_tindakanpr', 'kso', 'menejemen', 'total_byrdr', 'total_byrpr', 'total_byrdrpr']);

                    $calcTotalDr = $data['material'] + $data['bhp'] + $data['tarif_tindakandr'] + $data['kso'] + $data['menejemen'];
                    $calcTotalPr = $data['material'] + $data['bhp'] + $data['tarif_tindakanpr'] + $data['kso'] + $data['menejemen'];
                    $calcTotalDrPr = $data['material'] + $data['bhp'] + $data['tarif_tindakandr'] + $data['tarif_tindakanpr'] + $data['kso'] + $data['menejemen'];

                    $skipPr = $data['total_byrdrpr'] != 0 && $data['tarif_tindakanpr'] == 0;

                    $errors = [];

                    if ($data['total_byrdr'] != 0 && round($calcTotalDr) != round($data['total_byrdr'])) {
                        $errors[] = "Total Biaya Dr ({$data['total_byrdr']}) tidak sesuai. Rumus: Jasa Sarana + BHP + Jasa Medis Dr + KSO + Menejemen = {$calcTotalDr}";
                    }

                    if (! $skipPr && $data['total_byrpr'] != 0 && round($calcTotalPr) != round($data['total_byrpr'])) {
                        $errors[] = "Total Biaya Pr ({$data['total_byrpr']}) tidak sesuai. Rumus: Jasa Sarana + BHP + Jasa Medis Pr + KSO + Menejemen = {$calcTotalPr}";
                    }

                    if ($data['total_byrdrpr'] != 0 && round($calcTotalDrPr) != round($data['total_byrdrpr'])) {
                        $errors[] = "Total Biaya Dr & Pr ({$data['total_byrdrpr']}) tidak sesuai. Rumus: Jasa Sarana + BHP + Jasa Medis Dr + Jasa Medis Pr + KSO + Menejemen = {$calcTotalDrPr}";
                    }

                    if ($errors) {
                        throw new ImportTarifException("Baris {$line}: ".implode('; ', $errors));
                    }

                    try {

                        JenisPerawatanRanap::updateOrCreate(
                            ['kd_jenis_prw' => $data['kd_jenis_prw']],
                            [
                                'nm_perawatan'      => $data['nm_perawatan'],
                                'kd_kategori'       => $data['kd_kategori'],
                                'material'          => $data['material'],
                                'bhp'               => $data['bhp'],
                                'tarif_tindakandr'  => $data['tarif_tindakandr'],
                                'tarif_tindakanpr'  => $data['tarif_tindakanpr'],
                                'kso'               => $data['kso'],
                                'menejemen'         => $data['menejemen'],
                                'total_byrdr'       => $data['total_byrdr'],
                                'total_byrpr'       => $data['total_byrpr'],
                                'total_byrdrpr'     => $data['total_byrdrpr'],
                                'kd_pj'             => $data['kd_pj'],
                                'kd_bangsal'        => $data['kd_bangsal'],
                                'status'            => '1',
                                'kelas'             => $data['kelas'],
                            ]
                        );
                    } catch (QueryException $e) {
                        throw $this->saveFailed($e, 'jns_perawatan_inap', $line, $headerMapping, $raw);
                    }
                }

                tracker_end('mysql_sik', $this->userId);
            });

            Notification::make()
                ->message('Import tarif rawat inap berhasil')
                ->success()
                ->send($user);
        } catch (ImportTarifException $e) {
            Notification::make()->message($e->getMessage())->danger()->send($user);

            report($e);
            throw $e;
        } catch (Throwable $e) {
            Notification::make()
                ->message('Terjadi kesalahan saat mengimpor tarif rawat inap.')
                ->danger()
                ->send($user);

            report($e);
            throw $e;
        }
    }
}
