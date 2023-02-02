<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-set-hak-akses').on('shown.bs.modal', e => {
                        @this.emit('khanza.show-sha')
                    })

                    $('#modal-set-hak-akses').on('hide.bs.modal', e => {
                        @this.emit('khanza.hide-sha')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal :livewire="true" id="modal-set-hak-akses" title="Set Hak Akses User untuk SIMRS Khanza">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <div class="d-flex justify-content-start">
                    <div class="w-100">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                </div>
            </x-row-col>
            <x-row class="pt-2">
                <div class="col-12 table-responsive">
                    <x-table>
                        <x-slot name="columns">
                            <x-table.th title="#" style="width: 2rem" />
                            <x-table.th title="nama Field" />
                            <x-table.th title="Judul Menu" />
                        </x-slot>
                        <x-slot name="body">
                            @foreach ($this->hakAksesKhanza as $field => $judul)
                                <x-table.tr>
                                    <x-table.td>
                                        <input id="hak-akses-{{ $field }}" type="checkbox" wire:model.defer="checkedHakAkses" value="{{ $field }}">
                                        <label for="hak-akses-{{ $field }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                    </x-table.td>
                                    <x-table.td>{{ $field }}</x-table.td>
                                    <x-table.td>{{ $judul }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
            </x-row>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-button class="btn-default ml-auto" data-dismiss="modal" title="Batal" />
            <x-button class="btn-primary ml-2" data-dismiss="modal" wire:click="$emit('khanza.simpan')" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
