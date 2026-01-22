<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Keuangan\JenisPerawatan;
use App\Models\Keuangan\KategoriPerawatan;
use App\Models\Perawatan\Poliklinik;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class ImportTarifRalan extends Component
{
    use DeferredModal;
    use FlashComponent;
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    /** @var mixed */
    protected $listeners = [
        'tarif-ralan.hide-modal' => 'hideModal',
        'tarif-ralan.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.import-tarif-ralan');
    }

    public function importData(): void
    {
        if (user()->cannot('keuangan.tarif-ralan.create')) {
            $this->emit('flash-error', 'Anda tidak memiliki izin untuk mengimpor data tarif ralan.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->emit('flash-error', 'File import belum diunggah.');

            return;
        }

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

                $reader = SimpleExcelReader::create($this->fileImport->getRealPath());

                $headers = $reader->getHeaders();

                $missing = array_diff($requiredHeaders, $headers);

                if (! empty($missing)) {
                    throw ValidationException::withMessages([
                        'file' => 'Header tidak sesuai: '.implode(', ', $missing),
                    ]);
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

                    $validTotals = [
                        (float) $row['total_byrdr'],
                        (float) $row['total_byrpr'],
                        (float) $row['total_byrdrpr'],
                    ];

                    foreach ($validTotals as $total) {
                        if ($subtotal < $total) {
                            throw ValidationException::withMessages([
                                'row' => "Baris {$line}: subtotal tarif lebih kecil dari total",
                            ]);
                        }
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

                tracker_end('mysql_sik');
            });

            $this->emit('flash-success', 'Import tarif ralan berhasil');
            $this->dispatchBrowserEvent('data-saved');
            $this->defaultValues();

        } catch (ValidationException $e) {
            $this->flashError($e->getMessage());
        } catch (Throwable $e) {
            $this->flashError('Terjadi kesalahan saat mengimpor data tarif ralan: '.$e->getMessage());
        }
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
