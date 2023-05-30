<?php

namespace App\Http\Livewire\Keuangan\Modal;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalBackup;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UbahTanggalJurnal extends Component
{
    use DeferredModal, Filterable, FlashComponent;

    /** @var string */
    public $noJurnal;

    /** @var string */
    public $noBukti;

    /** @var string */
    public $keterangan;

    /** @var string */
    public $tglJurnalLama;

    /** @var string */
    public $jamJurnalLama;

    /** @var string */
    public $tglJurnalBaru;

    /** @var array */
    protected $rules = [
        'tglJurnalBaru' => ['required', 'date'],
    ];

    /** @var mixed */
    protected $listeners = [
        'utj.prepare' => 'prepareJurnal',
        'utj.show' => 'showModal',
        'utj.hide' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Keuangan\Jurnal\JurnalBackup>|array<empty, empty>
     */
    public function getBackupJurnalProperty()
    {
        return auth()->user()->cannot('keuangan.riwayat-jurnal-perbaikan.read')
            ? []
            : JurnalBackup::query()
                ->with('pegawai:nik,nama')
                ->where('no_jurnal', $this->noJurnal)
                ->orderByDesc('tgl_jurnal_diubah')
                ->get();
    }

    public function render(): View
    {
        return view('livewire.keuangan.modal.ubah-tanggal-jurnal');
    }

    public function prepareJurnal(array $data): void
    {
        $this->noJurnal = $data['noJurnal'];
        $this->noBukti = $data['noBukti'];
        $this->keterangan = $data['keterangan'];
        $this->tglJurnalLama = $data['tglJurnal'];
        $this->jamJurnalLama = $data['jamJurnal'];

        $this->tglJurnalBaru = $data['tglJurnal'];
    }

    public function updateTglJurnal(): void
    {
        if (!auth()->user()->can('keuangan.jurnal-perbaikan.ubah-tanggal')) {
            $this->flashError();

            return;
        }

        // $jurnalBackup = JurnalBackup::query()->where('no_jurnal', $this->noJurnal)->exists();
        $jurnalDiubah = Jurnal::query()->find($this->noJurnal);

        if (is_null($jurnalDiubah)) {
            throw (new ModelNotFoundException)->setModel(Jurnal::class, [$this->noJurnal]);
        }

        $jurnalTerakhirPerTgl = Jurnal::query()
            ->where('tgl_jurnal', $this->tglJurnalLama)
            ->orderByDesc(DB::raw('right(jurnal.no_jurnal, 6)'))
            ->value('no_jurnal');

        if ($jurnalDiubah->no_jurnal === $jurnalTerakhirPerTgl) {
            $this->flashError("Entri no. jurnal tidak boleh entri yang terakhir di tanggal {$jurnalDiubah->tgl_jurnal}! Silahkan tunggu beberapa saat lalu coba lagi.");

            return;
        }

        DB::transaction(function () use ($jurnalDiubah): void {
            tracker_start('mysql_sik');

            $jurnalDiubah->tgl_jurnal = carbon($this->tglJurnalBaru)->format('Y-m-d');

            $jurnalDiubah->save();

            tracker_end('mysql_sik');

            tracker_start('mysql_smc');

            JurnalBackup::create([
                'no_jurnal'         => $jurnalDiubah->no_jurnal,
                'tgl_jurnal_asli'   => $this->tglJurnalLama,
                'tgl_jurnal_diubah' => $jurnalDiubah->tgl_jurnal,
                'nip'               => auth()->user()->nik,
            ]);

            tracker_end('mysql_smc');
        });

        $this->dispatchBrowserEvent('jurnal-updated');
        $this->emitUp('flash.success', "Tgl. untuk no. jurnal {$this->noJurnal} berhasil diubah!");
    }

    public function restoreTglJurnal(int $backupId): void
    {
        if (!auth()->user()->can('keuangan.jurnal-perbaikan.ubah-tanggal')) {
            $this->flasError();

            return;
        }

        $tglJurnalKembali = null;

        DB::transaction(function () use ($backupId, $tglJurnalKembali) {
            $jurnalBackup = JurnalBackup::find($backupId);
            $jurnalSekarang = Jurnal::find($this->noJurnal);

            $tglJurnalKembali = $jurnalBackup->tgl_jurnal_asli;

            tracker_start('mysql_sik');

            $jurnalSekarang->tgl_jurnal = $jurnalBackup->tgl_jurnal_asli;

            $jurnalSekarang->save();

            tracker_end('mysql_sik');

            tracker_start('mysql_smc');

            $jurnalBackup->delete();

            tracker_end('mysql_smc');
        });

        $this->flashSuccess("No. jurnal {$this->noJurnal} dikembalikan ke tanggal {$tglJurnalKembali}!");
        $this->dispatchBrowserEvent('jurnal-restored');
    }

    protected function defaultValues(): void
    {
        $this->undefer();

        $this->noJurnal = '';
        $this->noBukti = '';
        $this->keterangan = '';
        $this->tglJurnalLama = '';
        $this->tglJurnalBaru = '';
        $this->jamJurnalLama = '';
    }
}
