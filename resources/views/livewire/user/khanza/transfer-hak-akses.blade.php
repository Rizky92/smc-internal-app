<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-khanza-transfer').on('shown.bs.modal', e => {
                        @this.emit('khanza.show-tha')
                    })

                    $('#modal-khanza-transfer').on('hide.bs.modal', e => {
                        @this.emit('khanza.hide-tha')
                    })

                    $('#checkbox-utama').change(e => {
                        let els = $('input[type=checkbox][id*=user-]')

                        els.each((i, el) => {
                            el.checked = e.target.checked
                        })
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire title="Transfer hak akses SIMRS Khanza ke user lainnya" id="modal-khanza-transfer">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <div class="d-flex justify-content-start">
                    <div class="w-100">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                </div>
            </x-row-col>
            <x-row-col class="pt-2">
                <div class="table-responsive">
                    <x-table>
                        <x-slot name="columns">
                            <x-table.th>
                                <input id="checkbox-utama" type="checkbox" name="__checkbox_utama" value="null">
                                <label for="checkbox-utama"></label>
                            </x-table.th>
                            <x-table.th title="NRP" />
                            <x-table.th title="Nama" />
                            <x-table.th title="Jabatan" />
                        </x-slot>
                        <x-slot name="body">
                            @foreach ($this->availableUsers as $user)
                                <x-table.tr>
                                    <x-table.td>
                                        <input id="user-{{ $user->nik }}" type="checkbox" wire:model.defer="checkedUsers" value="{{ $user->nik }}">
                                        <label for="user-{{ $user->nik }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                    </x-table.td>
                                    <x-table.td>{{ $user->nik }}</x-table.td>
                                    <x-table.td>{{ $user->nama }}</x-table.td>
                                    <x-table.td>{{ $user->jbtn }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search />
            <x-filter.toggle class="ml-1" title="Tampilkan yang dipilih" model="showChecked" />
            <x-button class="btn-default ml-auto" data-dismiss="modal" title="Batal" />
            <x-button class="btn-primary ml-2" data-dismiss="modal" wire:click="$emit('khanza.transfer')" title="Transfer" icon="fas fa-share-square" />
        </x-slot>
    </x-modal>
</div>
