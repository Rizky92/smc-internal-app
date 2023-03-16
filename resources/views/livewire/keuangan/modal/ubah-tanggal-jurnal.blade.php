<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-ubah-tgl-jurnal').on('shown.bs.modal', e => {
                        @this.emit('ptj.show')
                    })

                    $('#modal-ubah-tgl-jurnal').on('hide.bs.modal', e => {
                        @this.emit('ptj.hide')
                    })

                    Livewire.on('element.initialized', (el, component) => {
                        console.log({ el, component })

                        let readonlyNoJurnal = el.querySelector('input#no-jurnal')
                        let readonlyNoBukti = el.querySelector('input#no-bukti')
                        let readonlyKeterangan = el.querySelector('textarea#keterangan')

                        let inputTglJurnal = el.querySelector('input#tgl-jurnal')
                        let inputWaktuJurnal = el.querySelector('input#waktu-jurnal')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire id="modal-ubah-tgl-jurnal" title="Ubah tanggal jurnal untuk jurnal no." size="lg">
        <x-slot name="body">
            <x-row-col>
                <x-callout variant="danger" title="Perhatian!" content="Mengubah tanggal jurnal dapat menyebabkan kanker, serangan jantung, hipertensi, dan gangguan kehamilan dan janin" />
            </x-row-col>
            <form wire:submit.prevent="$emit('keuangan.saveJurnal')" class="mt-3">
                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="no-jurnal">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm" id="no-jurnal" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="no-bukti">No. Bukti</label>
                            <input type="text" class="form-control form-control-sm" id="no-bukti" readonly autocomplete="off">
                        </div>
                    </div>
                </x-row>
                <x-row-col>
                    <div class="form-group">
                        <label class="text-sm" for="keterangan">Keterangan</label>
                        <textarea class="form-control form-control-sm" id="keterangan" readonly style="resize: none"></textarea>
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button size="sm" title="Batal" data-dismiss="modal" wire:click="resetModal" />
            <x-button size="sm" variant="danger" class="ml-2" title="Simpan" icon="fas fa-save" data-dismiss="modal" />
        </x-slot>
    </x-modal>
</div>
