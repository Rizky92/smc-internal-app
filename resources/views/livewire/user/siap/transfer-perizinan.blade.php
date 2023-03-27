<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-transfer-perizinan').on('shown.bs.modal', e => {
                        @this.emit('siap.show-tp')
                    })

                    $('#modal-transfer-perizinan').on('hide.bs.modal', e => {
                        @this.emit('siap.hide-tp')
                    })

                    $('#modal-transfer-perizinan').on('hidden.bs.modal', e => {
                        $('#checkbox-utama-khanza-set').prop('checked', false)
                        $('#checkbox-utama-khanza-set').trigger('change')
                    })

                    $('#checkbox-utama-siap-transfer').change(e => {
                        let isChecked = e.target.checked
                        let els = $('input[type=checkbox][id*=tp-]')

                        let checkedUsers = new Map()

                        els.each((i, el) => {
                            el.checked = isChecked

                            checkedUsers.set(el.value, isChecked)
                        })

                        if (! isChecked) {
                            checkedUsers.clear()
                        }

                        @this.set('checkedUsers', Object.fromEntries(checkedUsers), true)
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire title="Transfer perizinan SIAP ke user lainnya" id="modal-transfer-perizinan">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col>
                <div class="d-flex justify-content-start px-3 pt-3">
                    <div class="w-50">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                    <div class="w-50">
                        <label>Hak akses yang ditransfer:</label>
                        <ul class="d-flex flex-wrap p-0 m-0 text-sm" style="list-style: none; row-gap: 0.5rem; column-gap: 0.25rem">
                            @foreach ($roles as $roleId => $role)
                                <li class="badge badge-dark text-sm font-weight-normal border">{{ $role }}</li>
                            @endforeach
                            @foreach ($permissions as $permissionId => $permission)
                                <li class="badge badge-light text-sm font-weight-normal border">{{ $permission }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </x-row-col>
            <x-row-col class="mt-2">
                <x-table zebra hover sticky nowrap>
                    <x-slot name="columns">
                        <x-table.th>
                            <input id="checkbox-utama-siap-transfer" type="checkbox" name="__checkbox_utama" value="null">
                            <label for="checkbox-utama-siap-transfer"></label>
                        </x-table.th>
                        <x-table.th title="NRP" />
                        <x-table.th title="Nama" />
                        <x-table.th title="Jabatan" />
                        <x-table.th title="Role" />
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($this->availableUsers as $user)
                            <x-table.tr>
                                <x-table.td>
                                    <input id="khanza-user-{{ $user->nik }}" type="checkbox" wire:model.defer="checkedUsers.{{ $user->nik }}" value="{{ $user->nik }}">
                                    <label for="khanza-user-{{ $user->nik }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                </x-table.td>
                                <x-table.td>{{ $user->nik }}</x-table.td>
                                <x-table.td>{{ $user->nama }}</x-table.td>
                                <x-table.td>{{ $user->jbtn }}</x-table.td>
                                <x-table.td>
                                    @foreach ($user->roles as $userRole)
                                        <span class="badge badge-dark font-weight-normal border">{{ $userRole->name }}</span>
                                    @endforeach
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="5" />
                        @endforelse
                    </x-slot>
                </x-table>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-filter.toggle class="ml-1" id="show-checked-siap-transfer" title="Tampilkan yang dipilih" model="showChecked" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" wire:click="$emit('siap.transfer')" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
