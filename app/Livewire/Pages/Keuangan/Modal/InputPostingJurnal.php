<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Models\Keuangan\Rekening;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalDetail;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InputPostingJurnal extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var string */
    public $noJurnalBaru;

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

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'posting-jurnal.hide-modal' => 'hideModal',
        'posting-jurnal.show-modal' => 'showModal',
    ];

    public function rules()
    {
        $rules = [
            'no_bukti'   => 'required|string',
            'tgl_jurnal' => 'required|date',
            'jam_jurnal' => 'required|string',
            'jenis'      => 'required|in:U,P',
            'keterangan' => 'required|string',
        ];

        foreach ($this->detail as $index => $detailItem) {
            $rules["detail.$index.kd_rek"]  = 'required|string';
            $rules["detail.$index.debet"]   = 'required|numeric';
            $rules["detail.$index.kredit"]  = 'required|numeric';
        }

        return $rules;
    }

    public function mount(Jurnal $jurnal): void
    {
        $this->noJurnalBaru = $jurnal->no_jurnal;
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

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.input-posting-jurnal', [
            'totalDebet'  => $this->calculateTotal('debet'),
            'totalKredit' => $this->calculateTotal('kredit'),
            'jurnalSementara' => $this->jurnalSementara,
        ]);
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

        $this->detail = $detail->isEmpty()
            ? []
            : $detail
                ->map(fn (JurnalDetail $model): array => [
                    'kd_rek' => $model->kd_rek,
                    'debet'  => round($model->debet),
                    'kredit' => round($model->kredit),
                ])
                ->all();
    }

    public function add(): void 
    {
        if (user()->cannot('keuangan.postin-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');
            return;
        }

        $this->validate();
        $this->validasiTotalDebitKredit();

        $jurnalSementaraData = [
            'no_bukti'   => $this->no_bukti,
            'tgl_jurnal' => $this->tgl_jurnal,
            'jam_jurnal' => $this->jam_jurnal,
            'jenis'      => $this->jenis,
            'keterangan' => $this->keterangan,
            'detail'     => $this->detail,
        ];

        $this->jurnalSementara[] = $jurnalSementaraData;

        $this->resetAdd();

    }
    
    public function create(): void
    {
        if (user()->cannot('keuangan.postin-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');
            return;
        }
    
        DB::beginTransaction();
    
        try {
            tracker_start();
    
            foreach ($this->jurnalSementara as $jurnalSementaraData) {
                $noJurnalBaru = Jurnal::noJurnalBaru($jurnalSementaraData['tgl_jurnal']);
    
                Jurnal::create([
                    'no_jurnal'   => $noJurnalBaru,
                    'no_bukti'    => $jurnalSementaraData['no_bukti'],
                    'tgl_jurnal'  => $jurnalSementaraData['tgl_jurnal'],
                    'jam_jurnal'  => $jurnalSementaraData['jam_jurnal'],
                    'jenis'       => $jurnalSementaraData['jenis'],
                    'keterangan'  => $jurnalSementaraData['keterangan'],
                ]);
    
                $jurnalDetailData = collect($jurnalSementaraData['detail'])->map(function ($detail) use ($noJurnalBaru) {
                    return [
                        'no_jurnal' => $noJurnalBaru,
                        'kd_rek'    => $detail['kd_rek'],
                        'debet'     => $detail['debet'],
                        'kredit'    => $detail['kredit'],
                    ];
                });
    
                JurnalDetail::insert($jurnalDetailData->toArray());

                PostingJurnal::updateOrCreate(
                    ['no_jurnal' => $noJurnalBaru],
                    [
                        'no_jurnal'  => $noJurnalBaru,
                        'tgl_jurnal' => $jurnalSementaraData['tgl_jurnal'],
                    ]
                );
            }
            
            tracker_end();
    
            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Posting Jurnal berhasil ditambahkan');
            $this->reset(['no_bukti', 'tgl_jurnal', 'keterangan']);
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->emit('flash.error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }

        $this->defaultValues();
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
        $totaldebit = round(floatval(collect($this->detail)->sum('debet')), 2);
        $totalkredit = round(floatval(collect($this->detail)->sum('kredit')), 2);

        if ($totaldebit != $totalkredit) {
            throw ValidationException::withMessages([
                'totalDebitKredit' => 'Total debit dan kredit tidak sesuai'
            ]);
        }
    }
}
