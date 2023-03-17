<?php

namespace App\Http\Livewire\Keuangan\Modal;

use App\Exceptions\ModelIsIdenticalException;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalBackup;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
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

            $this->return;
        }
        
        $jurnalDiubah = Jurnal::query()->find($this->noJurnal);
        $jurnalTerakhirPerTgl = Jurnal::findLatest($this->tglJurnalLama);

        if ($jurnalDiubah->is($jurnalTerakhirPerTgl)) {
            throw new ModelIsIdenticalException;

            tracker_end('mysql_sik');

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

        $this->dispatchBrowserEvent('data-saved');
        $this->emitUp('flash.success', "Tgl. untuk no. jurnal {$jurnalDiubah->no_jurnal} berhasil diubah!");
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

    protected function preventValuesFromBeingTampered()
    {

    }
}
