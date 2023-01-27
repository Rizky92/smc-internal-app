<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let inputNRP
                let inputNama
                let inputRoles
                let inputPermissions

                let buttonResetFilters

                $(document).ready(() => {
                    inputNRP = $('#user')
                    inputNama = $('#nama')
                    inputRoles = $('input[name=roles]')
                    inputPermissions = $('input[name=permissions]')
                    buttonResetFilters = $('button#reset-filters')

                    buttonResetFilters.click(e => {
                        inputNRP.val('')
                        inputNama.val('')
                        inputRoles.each((i, el) => el.checked = false)
                        inputPermissions.each((i, el) => el.checked = false)
                    })
                })

                function loadData({
                    nrp,
                    nama,
                    roleIds,
                    rolePermissionIds,
                    permissionIds
                }) {
                    inputNRP.val(nrp)
                    inputNama.val(nama)

                    let roles = []
                    let rolePermissions = []
                    let permissions = []

                    if (roleIds !== "") {
                        roles = Array.from(roleIds.split(','))
                    }

                    if (rolePermissionIds !== "") {
                        rolePermissions = Array.from(rolePermissionIds.split(','))
                    }

                    if (permissionIds !== "") {
                        permissions = Array.from(permissionIds.split(','))
                    }

                    inputRoles.each((i, el) => el.checked = roles.find(v => v === el.value))
                    inputPermissions.each((i, el) => {
                        let allPermissions = permissions.concat(rolePermissions)
                        
                        el.checked = allPermissions.find(v => v === el.value)
                    })

                    @this.emit('custom-report.prepare-user', nrp, nama, roles, permissions)
                    @this.emit('custom-report.prepare-transfer', nrp, nama, roles, permissions)
                    @this.emit('khanza.prepare-user', nrp, nama)
                    @this.emit('khanza.prepare-transfer', nrp, nama)
                }
            </script>
        @endpush
    @endonce

    <livewire:user.custom-report.set-role-permissions />

    <livewire:user.custom-report.transfer-role-permissions />

    <livewire:user.khanza.set-hak-akses />

    <livewire:user.khanza.transfer-hak-akses />

    <x-card>
        <x-slot name="header">
            <x-card.row :livewire="true">
                <div class="col-2">
                    <div class="form-group">
                        <label class="text-sm" for="user">NRP</label>
                        <input class="form-control form-control-sm" id="user" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="nama">Nama</label>
                        <input class="form-control form-control-sm" id="nama" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-end h-100">
                        <div class="dropdown mb-3">
                            <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-info-circle"></i>
                                <span class="ml-1">Set hak akses</span>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item text-sm" type="button" id="button-set-hak-akses" data-toggle="modal" data-target="#modal-set-hak-akses">
                                    SIMRS Khanza
                                </button>
                                <button class="dropdown-item text-sm" type="button" id="button-set-role-permissions" data-toggle="modal" data-target="#modal-set-role-permissions">
                                    Custom Report
                                </button>
                            </div>
                        </div>

                        <div class="dropdown mb-3 ml-3">
                            <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-share-square"></i>
                                <span class="ml-1">Transfer hak akses</span>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item text-sm" type="button" id="button-transfer-hak-akses" data-toggle="modal" data-target="#modal-transfer-hak-akses">
                                    SIMRS Khanza
                                </button>
                                <button class="dropdown-item text-sm" type="button" id="button-transfer-role-permissions" data-toggle="modal" data-target="#modal-transfer-role-permissions">
                                    Custom Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card.row>
            <x-card.row-col>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive p-0">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="nik" title="NRP" style="width: 15ch" />
                    <x-table.th name="nama" title="Nama" style="width: 50ch" />
                    <x-table.th name="jbtn" title="Jabatan" style="width: 30ch" />
                    <x-table.th name="jenis" title="Jenis" style="width: 10ch" />
                    <x-table.th>Hak Akses</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->users as $user)
                        <x-table.tr>
                            <x-table.td>
                                {{ $user->nik }}
                                <x-slot name="clickable"
                                    data-nrp="{{ $user->nik }}"
                                    data-nama="{{ $user->nama }}"
                                    data-role-ids="{{ $user->roles->pluck('id')->join(',') }}"
                                    data-role-permission-ids="{{ $user->getPermissionsViaRoles()->pluck('id')->join(',') }}"
                                    data-permission-ids="{{ $user->permissions->pluck('id')->join(',') }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $user->nama }}</x-table.td>
                            <x-table.td>{{ $user->jbtn }}</x-table.td>
                            <x-table.td>{{ $user->jenis }}</x-table.td>
                            <x-table.td>
                                @foreach ($user->roles as $role)
                                    <x-badge :class="Arr::toCssClasses(['badge-dark', 'ml-1' => !$loop->first], ' ')">{{ $role->name }}</x-badge>
                                @endforeach
                                @foreach ($user->permissions as $permission)
                                    <x-badge :class="Arr::toCssClasses(['badge-info', 'ml-1' => (!$loop->first || $user->roles->count() > 0)], ' ')">{{ $permission->name }}</x-badge>
                                @endforeach
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->users" />
        </x-slot>
    </x-card>
</div>
