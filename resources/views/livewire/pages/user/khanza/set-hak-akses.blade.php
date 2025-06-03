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

                    $('#modal-set-hak-akses').on('hidden.bs.modal', e => {
                        $('#checkbox-set-hak-akses').prop('checked', false)
                        $('#checkbox-set-hak-akses').trigger('change')
                    })
                })
            </script>
        @endpush
    @endonce

    <x-modal livewire title="Set hak akses user untuk SIMRS Khanza" id="modal-set-hak-akses">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <label class="mb-0">User:</label>
                <p>{{ "{$nrp} {$nama}" }}</p>
            </x-row-col>
            <x-row-col class="pt-2">
                <x-table zebra hover sortable :sortColumns="$sortColumns">
                    <x-slot name="columns">
                        <x-table.th-checkbox-all livewire class="pl-3" style="width: max-content" id="checkbox-set-hak-akses" name="__checkbox_sha_utama" model="checkedHakAkses" lookup="sha-" />
                        <x-table.th name="nama_field" title="Nama Field" />
                        <x-table.th name="judul_menu" title="Judul Menu" />
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($this->hakAksesKhanza as $hakAkses)
                            <x-table.tr>
                                <x-table.td-checkbox livewire class="pl-3" model="checkedHakAkses" :key="$hakAkses->nama_field" :id="$hakAkses->nama_field" prefix="sha-" />
                                <x-table.td>
                                    {{ $hakAkses->nama_field }}
                                </x-table.td>
                                <x-table.td>
                                    {{ $hakAkses->judul_menu }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="3" padding />
                        @endforelse
                    </x-slot>
                </x-table>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-filter.toggle class="ml-1" id="show-checked-set-hak-akses" title="Tampilkan yang dipilih" model="showChecked" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" wire:click="$emit('khanza.set')" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
