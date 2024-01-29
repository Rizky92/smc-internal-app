<div>
    @push('js')
        <script>
            $('#modal-input-posting-jurnal').on('shown.bs.modal', e => {
                @this.emit('posting-jurnal.show-modal')
            })

            $('#modal-input-posting-jurnal').on('hide.bs.modal', e => {
                @this.emit('posting-jurnal.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-posting-jurnal').modal('hide')
            })
        </script>
    @endpush
    <x-modal id="modal-input-posting-jurnal" :title="('Posting Jurnal Baru')" livewire centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-form id="form-input-posting-jurnal">
                <div class="form-group">
                    <label for="kode-rekening">Rekening:</label>
                    <x-form.select2 id="kode-rekening" model="kodeRekening" :options="$this->rekening" placeholder="-" width="full-width" />
                    <x-form.error name="kodeRekening" />
                </div>
                <div class="form-group d-flex justify-content-start align-items-center m-0 p-0">
                    <div class="form-group mt-3 ">
                        <label for="tgl_jurnal">Tgl. Jurnal</label>
                        <x-form.date model="tgl_jurnal" />
                        <x-form.error name="tgl_jurnal" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="jenis">Jenis</label>
                        <x-form.select2 id="jenis-jurnal-id" />
                        <x-form.error name="tglPakai" />
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" wire:model.defer="keterangan" class="form-control form-control-sm" />
                    <x-form.error name="keterangan" />
                </div>
                <div class="form-group mt-3">
                    <div class="d-flex justify-content-start align-items-center">
                        <span class="d-block font-weight-bold" style="width: calc(75% - 1.6rem)">Rekening</span>
                        <span class="d-block font-weight-bold">Debet</span>
                        <span class="d-block font-weight-bold">Kredit</span>
                    </div>
                    <ul class="p-0 m-0 mt-2 mb-3 d-flex flex-column" style="row-gap: 0.5rem" id="detail-pemakaian">
                    </ul>
                    <x-button size="sm" variant="secondary" title="Tambah Detail" icon="fas fa-plus" wire:click="addDetail" />
                    <div class="mt-1">
                        <x-form.error name="nominalPemakaian" />
                    </div>
                </div>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-posting-jurnal" />
        </x-slot>
    </x-modal>
</div>