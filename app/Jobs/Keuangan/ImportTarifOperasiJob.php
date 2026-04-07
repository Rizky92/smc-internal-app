<?php

namespace App\Jobs\Keuangan;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\PaketOperasi;
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

                $headerMapping = [
                    'Kode Paket'        => 'kode_paket',
                    'Nama Operasi'      => 'nm_perawatan',
                    'Kategori'          => 'kategori',
                    'Operator 1'        => 'operator1',
                    'Operator 2'        => 'operator2',
                    'Operator 3'        => 'operator3',
                    'Asisten Op 1'      => 'asisten_operator1',
                    'Asisten Op 2'      => 'asisten_operator2',
                    'Asisten Op 3'      => 'asisten_operator3',
                    'Instrumen'         => 'instrumen',
                    'dr Anestesi'       => 'dokter_anestesi',
                    'Asisten Anes 1'    => 'asisten_anestesi',
                    'Asisten Anes 2'    => 'asisten_anestesi2',
                    'dr Anak'           => 'dokter_anak',
                    'Perawat Resus'     => 'perawaat_resusitas',
                    'Bidan 1'           => 'bidan',
                    'Bidan 2'           => 'bidan2',
                    'Bidan 3'           => 'bidan3',
                    'Perawat Luar'      => 'perawat_luar',
                    'Alat'              => 'alat',
                    'Sewa OK/VK'        => 'sewa_ok',
                    'Akomodasi'         => 'akomodasi',
                    'N.M.S.'            => 'bagian_rs',
                    'Onloop 1'          => 'omloop',
                    'Onloop 2'          => 'omloop2',
                    'Onloop 3'          => 'omloop3',
                    'Onloop 4'          => 'omloop4',
                    'Onloop 5'          => 'omloop5',
                    'Sarpras'           => 'sarpras',
                    'dr Pj Anak'        => 'dokter_pjanak',
                    'dr Umum'           => 'dokter_umum',
                    'Jenis Bayar'       => 'kd_pj',
                    'Kelas'             => 'kelas',
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

                    $data['operator1'] = parse_numeric($data['operator1'] ?? 0);
                    $data['operator2'] = parse_numeric($data['operator2'] ?? 0);
                    $data['operator3'] = parse_numeric($data['operator3'] ?? 0);
                    $data['asisten_operator1'] = parse_numeric($data['asisten_operator1'] ?? 0);
                    $data['asisten_operator2'] = parse_numeric($data['asisten_operator2'] ?? 0);
                    $data['asisten_operator3'] = parse_numeric($data['asisten_operator3'] ?? 0);
                    $data['instrumen'] = parse_numeric($data['instrumen'] ?? 0);
                    $data['dokter_anestesi'] = parse_numeric($data['dokter_anestesi'] ?? 0);
                    $data['asisten_anestesi'] = parse_numeric($data['asisten_anestesi'] ?? 0);
                    $data['asisten_anestesi2'] = parse_numeric($data['asisten_anestesi2'] ?? 0);
                    $data['dokter_anak'] = parse_numeric($data['dokter_anak'] ?? 0);
                    $data['perawaat_resusitas'] = parse_numeric($data['perawaat_resusitas'] ?? 0);
                    $data['bidan'] = parse_numeric($data['bidan'] ?? 0);
                    $data['bidan2'] = parse_numeric($data['bidan2'] ?? 0);
                    $data['bidan3'] = parse_numeric($data['bidan3'] ?? 0);
                    $data['perawat_luar'] = parse_numeric($data['perawat_luar'] ?? 0);
                    $data['alat'] = parse_numeric($data['alat'] ?? 0);
                    $data['sewa_ok'] = parse_numeric($data['sewa_ok'] ?? 0);
                    $data['akomodasi'] = parse_numeric($data['akomodasi'] ?? 0);
                    $data['bagian_rs'] = parse_numeric($data['bagian_rs'] ?? 0);
                    $data['omloop'] = parse_numeric($data['omloop'] ?? 0);
                    $data['omloop2'] = parse_numeric($data['omloop2'] ?? 0);
                    $data['omloop3'] = parse_numeric($data['omloop3'] ?? 0);
                    $data['omloop4'] = parse_numeric($data['omloop4'] ?? 0);
                    $data['omloop5'] = parse_numeric($data['omloop5'] ?? 0);
                    $data['sarpras'] = parse_numeric($data['sarpras'] ?? 0);
                    $data['dokter_pjanak'] = parse_numeric($data['dokter_pjanak'] ?? 0);
                    $data['dokter_umum'] = parse_numeric($data['dokter_umum'] ?? 0);

                    try {
                        PaketOperasi::query()->updateOrCreate(
                            ['kode_paket' => $data['kode_paket']],
                            [
                                'nm_perawatan'       => $data['nm_perawatan'],
                                'kategori'           => $data['kategori'],
                                'operator1'          => $data['operator1'],
                                'operator2'          => $data['operator2'],
                                'operator3'          => $data['operator3'],
                                'asisten_operator1'  => $data['asisten_operator1'],
                                'asisten_operator2'  => $data['asisten_operator2'],
                                'asisten_operator3'  => $data['asisten_operator3'],
                                'instrumen'          => $data['instrumen'],
                                'dokter_anestesi'    => $data['dokter_anestesi'],
                                'asisten_anestesi'   => $data['asisten_anestesi'],
                                'asisten_anestesi2'  => $data['asisten_anestesi2'],
                                'dokter_anak'        => $data['dokter_anak'],
                                'perawaat_resusitas' => $data['perawaat_resusitas'],
                                'bidan'              => $data['bidan'],
                                'bidan2'             => $data['bidan2'],
                                'bidan3'             => $data['bidan3'],
                                'perawat_luar'       => $data['perawat_luar'],
                                'alat'               => $data['alat'],
                                'sewa_ok'            => $data['sewa_ok'],
                                'akomodasi'          => $data['akomodasi'],
                                'bagian_rs'          => $data['bagian_rs'],
                                'omloop'             => $data['omloop'],
                                'omloop2'            => $data['omloop2'],
                                'omloop3'            => $data['omloop3'],
                                'omloop4'            => $data['omloop4'],
                                'omloop5'            => $data['omloop5'],
                                'sarpras'            => $data['sarpras'],
                                'dokter_pjanak'      => $data['dokter_pjanak'],
                                'dokter_umum'        => $data['dokter_umum'],
                                'kd_pj'              => $data['kd_pj'] ?? '-',
                                'kelas'              => $data['kelas'] ?? '-',
                                'status'             => '1',
                            ]);
                    } catch (QueryException $e) {
                        throw new RuntimeException("Baris {$line}: Gagal menyimpan data ke database. Pastikan format data sudah benar.");
                    }
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
