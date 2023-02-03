<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-transfer-role-permissions').on('shown.bs.modal', e => {
                        @this.emit('custom-report.show-trp')
                    })

                    $('#modal-transfer-role-permissions').on('hide.bs.modal', e => {
                        @this.emit('custom-report.hide-trp')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal :livewire="true" title="Transfer Role Permission untuk Custom Report" id="modal-transfer-role-permissions">
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
                <div class="table-responsive">
                    <x-table>
                        <x-slot name="columns">
                            <x-table.th title="#" />
                            <x-table.th title="NRP" />
                            <x-table.th title="Nama" />
                            <x-table.th title="Jabatan" />
                            <x-table.th title="Role" />
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
                                    <x-table.td>
                                        @foreach ($user->roles as $userRole)
                                            <span class="badge badge-dark font-weight-normal border">{{ $userRole->name }}</span>
                                        @endforeach
                                    </x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search />
            <x-button class="btn-default ml-auto" data-dismiss="modal" title="Batal" />
            <x-button class="btn-primary ml-2" data-dismiss="modal" wire:click="$emit('custom-report.transfer')" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
