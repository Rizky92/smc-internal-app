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
                        $('#checkbox-transfer-perizinan').prop('checked', false)
                        $('#checkbox-transfer-perizinan').trigger('change')
                    })
                })
            </script>
        @endpush
    @endonce

    <x-modal livewire title="Transfer perizinan SIAP ke user lainnya" id="modal-transfer-perizinan">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="px-3 pt-3">
                <div class="d-flex justify-content-between" style="column-gap: 1rem">
                    <div class="w-50">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                    <div class="w-50">
                        <label>Hak akses yang ditransfer:</label>
                        <ul class="d-flex flex-wrap p-0 m-0 text-xs" style="list-style: none; row-gap: 0.5rem; column-gap: 0.25rem">
                            @foreach ($roles as $roleId => $role)
                                <li class="badge badge-dark text-sm font-weight-normal border">
                                    {{ $role }}
                                </li>
                            @endforeach

                            @foreach ($permissions as $permissionId => $permission)
                                <li class="badge badge-light text-sm font-weight-normal border">
                                    {{ $permission }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </x-row-col>
            <x-row-col class="pt-2">
                <x-table zebra hover>
                    <x-slot name="columns">
                        <x-table.th-checkbox-all livewire class="pl-3" style="width: max-content" id="checkbox-transfer-perizinan" name="__checkbox_tp_utama" model="checkedUsers" lookup="tp-" />
                        <x-table.th style="width: 5ch" title="NRP" />
                        <x-table.th title="Nama" />
                        <x-table.th title="Jabatan" />
                        <x-table.th title="Role" />
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($this->availableUsers as $user)
                            <x-table.tr>
                                <x-table.td-checkbox livewire class="pl-3" model="checkedUsers" :key="$user->nik" :id="$user->nik" prefix="tp-" />
                                <x-table.td>{{ $user->nik }}</x-table.td>
                                <x-table.td>{{ $user->nama }}</x-table.td>
                                <x-table.td>{{ $user->jbtn }}</x-table.td>
                                <x-table.td>
                                    @foreach ($user->roles as $userRole)
                                        <span class="badge badge-dark font-weight-normal border">
                                            {{ $userRole->name }}
                                        </span>
                                    @endforeach
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="5" padding />
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
