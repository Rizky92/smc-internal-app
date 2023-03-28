<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-set-perizinan').on('shown.bs.modal', e => {
                        @this.emit('siap.show-sp')
                    })

                    $('#modal-set-perizinan').on('hide.bs.modal', e => {
                        @this.emit('siap.hide-sp')
                    })

                    $('#modal-set-perizinan').on('hidden.bs.modal', e => {
                        $('#checkbox-set-perizinan').prop('checked', false)
                        $('#checkbox-set-perizinan').trigger('change')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire title="Set perizinan user untuk SIAP" id="modal-set-perizinan">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <div class="d-flex justify-content-start">
                    <div class="w-100">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                </div>
            </x-row-col>
            <x-row-col>
                <x-navtabs livewire class="pt-3" selected="pilih-perizinan">
                    <x-slot name="tabs">
                        <x-navtabs.tab id="pilih-perizinan" title="Pilih Perizinan" />
                        <x-navtabs.tab id="pilih-dari-role" title="Pilih Dari Role" />
                    </x-slot>
                    <x-slot name="contents">
                        <x-navtabs.content id="pilih-perizinan">
                            <div class="table-responsive">
                                <x-table>
                                    <x-slot name="columns">
                                        <x-table.th class="px-3">
                                            <input id="checkbox-set-perizinan" type="checkbox" name="__checkbox_utama">
                                            <label for="checkbox-set-perizinan"></label>
                                        </x-table.th>
                                        <x-table.th title="Nama Perizinan" />
                                    </x-slot>
                                    <x-slot name="body">
                                        @forelse ($this->permissions as $key => $name)
                                            <x-table.tr>
                                                <x-table.td class="px-3">
                                                    <input id="sk-{{ $key }}" type="checkbox" wire:model.defer="checkedPermissions.{{ $key }}">
                                                    <label for="sk-{{ $key }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                                </x-table.td>
                                                <x-table.td>{{ $name }}</x-table.td>
                                            </x-table.tr>
                                        @empty
                                            <x-table.tr-empty colspan="2" />
                                        @endforelse
                                    </x-slot>
                                </x-table>
                            </div>
                        </x-navtabs.content>
                        <x-navtabs.content id="pilih-dari-role">
                            <x-table zebra hover sticky nowrap>
                                <x-slot name="columns">
                                    <x-table.th class="px-3">
                                        <input id="checkbox-set-role" type="checkbox" name="__checkbox_utama">
                                        <label for="checkbox-set-role"></label>
                                    </x-table.th>
                                    <x-table.th title="Nama Role" />
                                    <x-table.th title="Perizinan yang diberikan" />
                                </x-slot>
                                <x-slot name="body">
                                    @forelse ($this->roles as $role)
                                        <x-table.tr>
                                            <x-table.td class="px-3">
                                                <input id="sr-{{ $role->id }}" type="checkbox" wire:model.defer="checkedRoles.{{ $role->id }}">
                                                <label for="sr-{{ $role->id }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                            </x-table.td>
                                            <x-table.td>{{ optional($role)->name }}</x-table.td>
                                            <x-table.td>
                                                @unless(optional($role)->name === config('permission.superadmin_name'))
                                                    <div style="display: inline-flex; flex-wrap: wrap; gap: 0.25rem">
                                                        @foreach (optional($role)->permissions ?? [] as $permission)
                                                            <x-badge variant="secondary">{{ $permission->name }}</x-badge>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <x-badge variant="dark">*</x-badge>
                                                @endunless
                                            </x-table.td>
                                        </x-table.tr>
                                    @empty
                                        <x-table.tr-empty colspan="3" />
                                    @endforelse
                                </x-slot>
                            </x-table>
                        </x-navtabs.content>
                    </x-slot>
                </x-navtabs>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-filter.toggle class="ml-1" id="show-checked-set-perizinan" title="Tampilkan yang dipilih" model="showChecked" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" wire:click="$emit('khanza.set')" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
