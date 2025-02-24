<div>
    @push('js')
        <script>
            $('#modal-input-penetapan-rkat').on('shown.bs.modal', e => {
                @this.emit('penetapan-rkat.show-modal')
            })

            $('#modal-input-penetapan-rkat').on('hide.bs.modal', e => {
                @this.emit('penetapan-rkat.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-penetapan-rkat').modal('hide')
            })
        </script>
    @endpush

    @push('css')
        @once
            <style>
                .w-40 {
                    width: 40% !important;
                }

                .w-20 {
                    width: 20% !important;
                }

                input::-webkit-outer-spin-button,
                input::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }

                /* Firefox */
                input[type='number'] {
                    -moz-appearance: textfield;
                }
            </style>
        @endonce
    @endpush

    <x-modal id="modal-input-penetapan-rkat" :title="($this->isUpdating() ? 'Edit' : 'Input') . ' Data Anggaran Tahun ' . $this->tahun" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-penetapan-rkat" livewire submit="create" class="py-1 px-3">
                <x-row-col-flex col-gap="1rem">
                    <div class="form-group w-100">
                        <label for="bidang-id">Bidang:</label>
                        <x-form.select id="bidang-id" model="bidangId" :options="$this->bidangUnit" placeholder="-" width="full-width" />
                        <x-form.error name="bidangId" />
                    </div>
                    <div class="form-group w-100">
                        <label for="anggaran-id">Kategori Anggaran:</label>
                        <x-form.select id="anggaran-id" model="anggaranId" :options="$this->kategoriAnggaran" placeholder="-" width="full-width" />
                        <x-form.error name="anggaranId" />
                    </div>
                </x-row-col-flex>
                <x-row-col class="mt-3">
                    <div class="form-group">
                        <label for="nominal-anggaran">Nominal Anggaran</label>
                        <div class="d-flex">
                            <span class="mt-1">Rp.</span>
                            <input type="text" id="nominal-anggaran" wire:model.defer="nominalAnggaran" class="form-control form-control-sm ml-3 text-right" placeholder="0" />
                        </div>
                        <x-form.error name="nominalAnggaran" />
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            @if ($this->isUpdating() && user()->can('keuangan.rkat-penetapan.delete'))
                <x-button size="sm" variant="danger" data-dismiss="modal" id="hapus" title="Hapus" icon="fas fa-trash" wire:click="delete" />
            @endif

            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-penetapan-rkat" />
        </x-slot>
    </x-modal>
</div>
