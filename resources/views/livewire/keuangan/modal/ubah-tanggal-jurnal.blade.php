<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-ubah-tgl-jurnal').on('shown.bs.modal', e => {
                        @this.emit('utj.show')
                    })

                    $('#modal-ubah-tgl-jurnal').on('hide.bs.modal', e => {
                        @this.emit('utj.hide')
                    })

                    $(document).on('data-saved', e => {
                        $('#modal-ubah-tgl-jurnal').modal('hide')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire id="modal-ubah-tgl-jurnal" title="Ubah Tanggal Jurnal" size="lg">
        <x-slot name="body">
            <x-flash />
            <x-row-col>
                <x-callout variant="warning">
                    <x-slot name="title">Perhatian!</x-slot>
                    <x-slot name="content">
                        Mengubah tanggal jurnal dapat mempengaruhi kegiatan penjurnalan yang sedang berjalan.
                    </x-slot>
                </x-callout>
            </x-row-col>
            <form wire:submit.prevent="$emit('utj.save')" class="mt-3" id="form-ubah-tgl-jurnal">
                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="no-jurnal">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm" id="no-jurnal" wire:model.defer="noJurnal" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="no-bukti">No. Bukti</label>
                            <input type="text" class="form-control form-control-sm" id="no-bukti" wire:model.defer="noBukti" readonly autocomplete="off">
                        </div>
                    </div>
                </x-row>
                <x-row-col>
                    <div class="form-group">
                        <label class="text-sm" for="keterangan">Keterangan</label>
                        <textarea class="form-control form-control-sm" id="keterangan" wire:model.defer="keterangan" readonly style="resize: none"></textarea>
                    </div>
                </x-row-col>
                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="tgl-jurnal-lama">Tgl. Jurnal lama</label>
                            <input type="date" class="form-control form-control-sm" id="tgl-jurnal-lama" wire:model.defer="tglJurnalLama" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="tgl-jurnal-baru">Tgl. Jurnal BARU</label>
                            <input type="date" class="form-control form-control-sm" id="tgl-jurnal-baru" autocomplete="off" wire:model.defer="tglJurnalBaru">
                        </div>
                    </div>
                </x-row>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button size="sm" title="Batal" data-dismiss="modal" wire:click.prevent="$emit('utj.hide')" />
            <x-button type="submit" size="sm" variant="danger" class="ml-2" title="Simpan" icon="fas fa-save" form="form-ubah-tgl-jurnal" />
        </x-slot>
    </x-modal>
</div>
