<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Models\Keuangan\Rekening;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalDetail;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InputPostingJurnal extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var string */
    public $kodeRekening;

    /** @var array */
    public $detail;

    /** @var "U"|"P" */
    public $jenis;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'posting-jurnal.hide-modal' => 'hideModal',
        'posting-jurnal.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void 
    {
        $this->emit('select2.hydrate');
    }

    public function getRekeningProperty()
    {
        return Rekening::query()
        ->get()
                ->mapWithKeys(function (Rekening $r): array {
                $kd_rek = $r->kd_rek;
                $nm_rek = $r->nm_rek;
                $balance = $r->balance;

                $string = collect([$kd_rek, $nm_rek, $balance])
                    ->joinStr(' - ')
                    ->value();

                return [$r->kd_rek => $string];
            }); 
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.input-posting-jurnal', [
            'totalDebet' => $this->calculateTotal('debet'),
            'totalKredit' => $this->calculateTotal('kredit'),
        ]);
    }
    
    public function prepare(array $options): void
    {
        $this->kodeRekening = $options['kd_rek'] ?? -1;

        $detail = JurnalDetail::query()
            ->where('kd_rek', $this->kodeRekening)
            ->get();
    
        $this->detail = $detail->isEmpty()
            ? []
            : $detail
                ->map(fn (JurnalDetail $model): array => [
                    'kodeRekening' => $model->kodeRekening,
                    'debet' => round($model->debet),
                    'kredit' => round($model->kredit),
                ])
                ->all();
    }

    public function create():void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (user()->cannot('keuangan.postin-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();
        $this->validasiTotalDebitKredit();

        tracker_start();
        $postingJurnal = Jurnal::create([
            'no_jurnal'         => $this->no_jurnal,
            'tgl_jurnal'        => $this->tgl_jurnal,
            'jam_jurnal'        => $this->jam_jurnal,
            'jenis'             => $this->jenis,
            'keterangan'        => $this->keterangan,
        ]);

        $detailJurnal
            ->detail()
            ->createMany($this->detail);
        
        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Posting Jurnal baru berhasil ditambahkan!');
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
            'kd_rek'    => '',
            'debet'     => 0,
            'kredit'     => 0,
        ];
    }

    public function removeDetail(int $index): void
    {
        unset($this->detail[$index]);
    }

    protected function defaultValues(): void
    {
        $this->jenis = 'U';
        $this->detail = [[
            'kd_rek'    => '',
            'debet'     => 0,
            'kredit'     => 0,
        ]];
    }

    private function validasiTotalDebitKredit(): void
    {
        $totaldebit = round(floatval(collect($this->detail)->sum('debit')), 2);

        $totalkredit = round(floatval(collect($this->detail)->sum('kredit')), 2);


        if ($totaldebit != totalkredit) {
            throw ValidationException::withMessages([
                'totalDebitKredit' => 'Total debit dan kredit tidak sesuai'
            ]);
        }
    }
}
