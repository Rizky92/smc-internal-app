<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Models\Keuangan\Rekening;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalDetail;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InputPostingJurnal extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var string */
    public $no_bukti;

    /** @var string */
    public $jam_jurnal;

    /** @var string */
    public $keterangan;

    /** @var string */
    public $tgl_jurnal;
    
    /** @var array */
    public $detail;

    /** @var array */
    public $jurnalSementara = [];

    /** @var "U"|"P" */
    public $jenis;

    /** @var array */
    public $savedData;

    /** @var numeric */
    public $totalDebet;

    /** @var numeric */
    public $totalKredit;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'posting-jurnal.hide-modal' => 'hideModal',
        'posting-jurnal.show-modal' => 'showModal',
    ];

    /** @var mixed */
    public $rules = [
        'no_bukti'        => ['required', 'string', 'max:20'],
        'tgl_jurnal'      => ['required', 'date'],
        'jam_jurnal'      => ['required', 'string'],
        'jenis'           => ['required', 'in:U,P'],
        'keterangan'      => ['required', 'string'],
        'detail'          => ['array'],
        'detail.*.kd_rek' => ['required', 'string'],
        'detail.*.debet'  => ['required', 'numeric'],
        'detail.*.kredit' => ['required', 'numeric'],
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void
    {
        $this->emit('select2.hydrate');
    }

    public function getRekeningProperty(): array
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function getRekeningName($kdRek)
    {
        return $this->rekening[$kdRek] ?? '';
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.input-posting-jurnal');
    }

    public function prepare(array $options): void
    {
        $jurnal = Jurnal::query()
            ->where('no_jurnal', $this->noJurnalBaru)
            ->first();

        $this->no_bukti = $jurnal ? $jurnal->no_bukti : '';
        $this->tgl_jurnal = $jurnal ? $jurnal->tgl_jurnal : '';
        $this->jam_jurnal = $jurnal ? $jurnal->jam_jurnal : '';
        $this->jenis = $jurnal ? $jurnal->jenis : '';
        $this->keterangan = $jurnal ? $jurnal->keterangan : '';

        $detail = JurnalDetail::query()
            ->where('no_jurnal', $this->noJurnalBaru)
            ->get();

        $this->detail = $detail->isEmpty() ? [] : $detail
            ->map(fn (JurnalDetail $model): array => [
                'kd_rek' => $model->kd_rek,
                'debet'  => $model->debet,
                'kredit' => $model->kredit,
            ])
            ->all();
    }

    public function add(): void 
    {
        if (user()->cannot('keuangan.posting-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');
            return;
        }

        $this->validate();
        $this->validasiTotalDebitKredit();

        $this->jurnalSementara[] = [
            'no_bukti'   => $this->no_bukti,
            'tgl_jurnal' => $this->tgl_jurnal,
            'jam_jurnal' => $this->jam_jurnal,
            'jenis'      => $this->jenis,
            'keterangan' => $this->keterangan,
            'detail'     => $this->detail,
        ];

        $this->resetAdd();
    }

    public function hapusJurnalSementara($index)
    {
        if (isset($this->jurnalSementara[$index])) {
            unset($this->jurnalSementara[$index]);
        }
    }
    
    public function create(): void
    {
        if (user()->cannot('keuangan.posting-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');
            return;
        }

        $jurnalTercatat = [];

        try {
            DB::connection('mysql_sik')->transaction(function () use (&$jurnalTercatat) {
                tracker_start('mysql_sik');
    
                foreach ($this->jurnalSementara as $temp) {
                    $jurnal = Jurnal::catat(
                        $temp['no_bukti'],
                        str($temp['keterangan'])->upper()->trim()->replaceLast('.', '')->append(', OLEH ' . user()->nik),
                        carbon($temp['tgl_jurnal'])->setTimeFromTimeString($temp['jam_jurnal']),
                        $temp['detail']
                    );
    
                    $jurnalTercatat[] = [
                        'no_jurnal' => $jurnal->no_jurnal,
                        'tgl_jurnal' => $temp['tgl_jurnal'],
                    ];
                }
    
                tracker_end('mysql_sik');
            });
    
            tracker_start('mysql_smc');
    
            PostingJurnal::insert($jurnalTercatat);
    
            tracker_end('mysql_smc');
        } catch (Exception $_) {
            $this->flashError('Terjadi kesalahan saat menyimpan data');
            $this->dispatchBrowserEvent('data-denied');
            $this->defaultValues();
        }

        session()->put('savedData', $jurnalTercatat);

        $this->redirect('cetak-pdf-posting-jurnal');

        $this->defaultValues();
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Posting Jurnal berhasil ditambahkan');
    }
     
    private function calculateTotal($field): float
    {
        return collect($this->detail)->sum(function ($item) use ($field) {
            return $item[$field];
        });
    }

    public function addDetail(): void
    {
        $this->detail[] = [
            'kd_rek' => '',
            'debet'  => 0,
            'kredit' => 0,
        ];

        $this->emit('detailAdded');
    }

    public function removeDetail(int $index): void
    {
        unset($this->detail[$index]);
        $this->detail = array_values($this->detail);
    }

    public function isUpdating(): bool
    {
        return $this->noJurnalBaru !== -1;
    }

    protected function defaultValues(): void
    {
        $this->no_bukti = '';
        $this->keterangan = '';
        $this->jenis = 'U';
        $this->tgl_jurnal = now()->format('Y-m-d');
        $this->jam_jurnal = now()->format('H:i:s');
        $this->detail = [
            [
                'kd_rek' => '',
                'debet'  => 0,
                'kredit' => 0,
            ]
        ];
        $this->totalDebet = 0;
        $this->totalKredit = 0;
    }

    public function resetData(): void
    {
        $this->reset(['no_bukti', 'tgl_jurnal', 'jenis', 'jam_jurnal', 'keterangan', 'detail', 'jurnalSementara']);
        $this->defaultValues();
    }

    protected function resetAdd(): void
    {
        $this->keterangan = '';
        $this->jenis = 'U';
        $this->detail = [
            [
                'kd_rek' => '',
                'debet'  => 0,
                'kredit' => 0,
            ]
        ];
    }

    private function validasiTotalDebitKredit(): void
    {
        $totalDebit = collect($this->detail)->sum('debet');
        $totalKredit = collect($this->detail)->sum('kredit');
    
        if ($totalDebit != $totalKredit) {
            throw ValidationException::withMessages([
                'totalDebitKredit' => 'Total debet dan total kredit harus balance...!!!'
            ]);
        }
    
        if ($totalDebit == 0 || $totalKredit == 0) {
            throw ValidationException::withMessages([
                'totalDebitKredit' => 'Debet atau kredit tidak boleh kosong...!!!'
            ]);
        }
    }    
}
