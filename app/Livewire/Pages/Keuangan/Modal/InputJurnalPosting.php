<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use App\Models\Keuangan\Rekening;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InputJurnalPosting extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    /** @var string */
    public $no_bukti;

    /** @var string */
    public $tgl_jurnal;

    /** @var string */
    public $jam_jurnal;

    /** @var "U"|"P" */
    public $jenis;

    /** @var string */
    public $keterangan;

    /** @var array */
    public $detail;

    /** @var array */
    public $jurnalSementara = [];

    /** @var float|int */
    public $totalDebet;

    /** @var float|int */
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
        'detail.*.kd_rek' => ['string'],
        'detail.*.debet'  => ['numeric'],
        'detail.*.kredit' => ['numeric'],
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void
    {
        $this->emit('select2.hydrate');
    }

    public function getRekeningProperty(): Collection
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek');
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.input-jurnal-posting');
    }

    public function add(): void
    {
        $this->totalDebet = collect($this->detail)->sum('debet');
        $this->totalKredit = collect($this->detail)->sum('kredit');

        $this->detail[] = [
            'kd_rek' => '',
            'debet'  => 0,
            'kredit' => 0,
        ];

        $this->emit('detailAdded');
    }

    protected function validateBalance(): void
    {
        $totalDebet = (int) round(collect($this->detail)->sum('debet'), 0);
        $totalKredit = (int) round(collect($this->detail)->sum('kredit'), 0);
        $balance = $totalDebet <=> $totalKredit;

        if (! $balance == 0) {
            throw ValidationException::withMessages(['totalDebetKredit' => 'Debet dan Kredit harus sama!']);
        }
    }

    public function push(): void
    {
        if (user()->cannot('keuangan.posting-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();
        $this->validateBalance();

        $this->jurnalSementara[] = [
            'no_bukti'   => $this->no_bukti,
            'tgl_jurnal' => $this->tgl_jurnal,
            'jam_jurnal' => $this->jam_jurnal,
            'jenis'      => $this->jenis,
            'keterangan' => $this->keterangan,
            'detail'     => collect($this->detail)->reject(
                fn (array $v): bool => empty($v['kd_rek']) && empty($v['debet']) && empty($v['kredit'])
            )->values()->all(),
        ];

        $this->keterangan = '';
        $this->detail = [[
            'kd_rek' => '',
            'debet'  => 0,
            'kredit' => 0,
        ]];
        $this->totalDebet = 0;
        $this->totalKredit = 0;

        $this->emit('detailAdded');
    }

    public function pop(int $index): void
    {
        if (isset($this->jurnalSementara[$index])) {
            unset($this->jurnalSementara[$index]);
        }
    }

    /**
     * @return void
     */
    public function create()
    {
        if (user()->cannot('keuangan.posting-jurnal.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $validator = Validator::make(['jurnalSementara' => $this->jurnalSementara], [
            'jurnalSementara'                   => ['array'],
            'jurnalSementara.*.no_bukti'        => ['required', 'string', 'max:20'],
            'jurnalSementara.*.tgl_jurnal'      => ['required', 'date_format:Y-m-d'],
            'jurnalSementara.*.jam_jurnal'      => ['required', 'string'],
            'jurnalSementara.*.jenis'           => ['required', 'in:U,P'],
            'jurnalSementara.*.keterangan'      => ['required', 'string'],
            'jurnalSementara.*.detail'          => ['array'],
            'jurnalSementara.*.detail.*.kd_rek' => ['required', 'string'],
            'jurnalSementara.*.detail.*.debet'  => ['required', 'numeric'],
            'jurnalSementara.*.detail.*.kredit' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            $this->emit('flash.error', 'Tidak dapat melakukan proses posting jurnal!');
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
                        str($temp['keterangan'])->upper()->trim()->replaceLast('.', '')->append(', DIPOSTING OLEH '.user()->nik)->value(),
                        carbon($temp['tgl_jurnal'])->setTimeFromTimeString($temp['jam_jurnal']),
                        $temp['detail']
                    );

                    $jurnalTercatat[] = [
                        'no_jurnal'  => $jurnal->no_jurnal,
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

            return;
        }

        $this->redirectRoute('admin.keuangan.cetak-posting-jurnal', [
            'data_jurnal' => base64_encode(collect($jurnalTercatat)->pluck('no_jurnal')->toJson()),
        ]);
    }

    protected function defaultValues(): void
    {
        $this->no_bukti = '';
        $this->tgl_jurnal = now()->toDateString();
        $this->jam_jurnal = now()->format('H:i:s');
        $this->jenis = 'U';
        $this->keterangan = '';
        $this->totalDebet = 0;
        $this->totalKredit = 0;
        $this->detail = [[
            'kd_rek' => '',
            'debet'  => 0,
            'kredit' => 0,
        ]];
    }
}
