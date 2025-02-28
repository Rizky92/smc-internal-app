<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-transfer-hak-akses').on('shown.bs.modal', e => {
                        @this.emit('khanza.show-tha')
                    })

                    $('#modal-transfer-hak-akses').on('hide.bs.modal', e => {
                        @this.emit('khanza.hide-tha')
                    })

                    $('#modal-transfer-hak-akses').on('hidden.bs.modal', e => {
                        $('#checbox-transfer-hak-akses').prop('checked', false)
                        $('#checbox-transfer-hak-akses').trigger('change')
                    })
                })
            </script>
        @endpush
    @endonce

    <x-modal livewire title="Transfer hak akses SIMRS Khanza ke user lainnya" id="modal-transfer-hak-akses">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <label class="mb-0">User:</label>
                <p>{{ "{$nrp} {$nama}" }}</p>
                <x-filter.toggle class="mt-2" id="toggle-khanza-soft-transfer" title="Hanya transfer hak akses yang dimiliki user" model="softTransfer" />
            </x-row-col>
            <x-row-col class="pt-2">
                <x-table zebra hover>
                    <x-slot name="columns">
                        <x-table.th-checkbox-all livewire class="pl-3" style="width: max-content" id="checkbox-transfer-hak-akses" name="__checkbox_tha_utama" model="checkedUsers" lookup="tha-" />
                        <x-table.th style="width: 5ch" title="NRP" />
                        <x-table.th title="Nama" />
                        <x-table.th title="Jabatan" />
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($this->availableUsers as $user)
                            <x-table.tr>
                                <x-table.td-checkbox livewire class="pl-3" model="checkedUsers" :key="$user->nik" :id="$user->nik" prefix="tha-" />
                                <x-table.td>{{ $user->nik }}</x-table.td>
                                <x-table.td>{{ $user->nama }}</x-table.td>
                                <x-table.td>{{ $user->jbtn }}</x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="4" padding />
                        @endforelse
                    </x-slot>
                </x-table>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-filter.toggle class="ml-1" id="show-checked-khanza-transfer" title="Tampilkan yang dipilih" model="showChecked" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" wire:click="$emit('khanza.transfer')" title="Transfer" icon="fas fa-share-square" />
        </x-slot>
    </x-modal>
</div>
