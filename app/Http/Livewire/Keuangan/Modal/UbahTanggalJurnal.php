<?php

namespace App\Http\Livewire\Keuangan\Modal;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalBackup;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UbahTanggalJurnal extends Component
{
    use DeferredModal, Filterable, FlashComponent;

    public $dataJurnal;

    public $noJurnal;

    public $noBukti;

    public $keterangan;

    public $tglJurnalLama;

    public $jamJurnalLama;

    public $tglJurnalBaru;

    protected $rules = [
        'tglJurnalBaru' => ['required', 'date'],
    ];

    protected $listeners = [
        'utj.prepare' => 'prepareJurnal',
        'utj.show' => 'showModal',
        'utj.hide' => 'hideModal',
        'utj.save' => 'updateTglJurnal',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

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

    public function render()
    {
        return view('livewire.keuangan.modal.ubah-tanggal-jurnal');
    }

    public function prepareJurnal($data)
    {
        $this->dataJurnal = $data;
        $this->noJurnal = $data['noJurnal'];
        $this->noBukti = $data['noBukti'];
        $this->keterangan = $data['keterangan'];
        $this->tglJurnalLama = $data['tglJurnal'];
        $this->jamJurnalLama = $data['jamJurnal'];
        
        $this->tglJurnalBaru = $data['tglJurnal'];
    }

    public function updateTglJurnal()
    {
        if (! auth()->user()->can('keuangan.perbaikan-tgl-jurnal.ubah-tanggal')) {
            $this->flashError();

            return;
        }

        $jurnalDiubah = Jurnal::query()->find($this->noJurnal);
        $jurnalTerakhirPerTgl = Jurnal::query()->where('tgl_jurnal', $this->tglJurnalLama)->max('no_jurnal');

        if ($jurnalDiubah->no_jurnal === $jurnalTerakhirPerTgl) {
            $this->flashError("Entri no. jurnal tidak boleh entri yang terakhir di tanggal {$jurnalDiubah->tgl_jurnal}!");

            return;
        }

        tracker_start('mysql_sik');

        $jurnalDiubah->tgl_jurnal = carbon($this->tglJurnalBaru)->format('Y-m-d');

        $jurnalDiubah->save();

        tracker_end('mysql_sik');

        tracker_start('mysql_smc');

        JurnalBackup::create([
            'no_jurnal' => $jurnalDiubah->no_jurnal,
            'tgl_jurnal_asli' => $this->tglJurnalLama,
            'tgl_jurnal_diubah' => $jurnalDiubah->tgl_jurnal,
            'nip' => auth()->user()->nik,
        ]);

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', "Tgl. untuk no. jurnal {$jurnalDiubah->no_jurnal} berhasil diubah!");
    }

    public function restoreTglJurnal($backupId)
    {
        if (! auth()->user()->can('keuangan.perbaikan-tgl-jurnal.ubah-tanggal')) {
            $this->flasError();

            return;
        }

        $jurnalBackup = JurnalBackup::find($backupId);

        $jurnalSekarang = Jurnal::find($this->noJurnal);

        tracker_start('mysql_sik');

        $jurnalSekarang->tgl_jurnal = $jurnalBackup->tgl_jurnal_asli;

        $jurnalSekarang->save();

        tracker_end('mysql_sik');

        tracker_start('mysql_smc');

        $jurnalBackup->delete();

        tracker_end('mysql_smc');

        $this->flashSuccess("Data jurnal dikembalikan ke tanggal {$jurnalSekarang->tgl_jurnal}!");
        $this->emitUp('$refresh');
    }

    protected function defaultValues()
    {
        $this->undefer();

        $this->dataJurnal = [];
        $this->noJurnal = '';
        $this->noBukti = '';
        $this->keterangan = '';
        $this->tglJurnalLama = '';
        $this->tglJurnalBaru = '';
        $this->jamJurnalLama = '';
    }
}
