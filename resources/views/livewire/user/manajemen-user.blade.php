<div>
    <x-flash />

    @once
        @push('js')
            <script>
                const inputNRP = $('input#user')
                const inputNama = $('input#nama')
                const inputRoles = $('input[name=roles]')
                const inputRolePermissions = $('input[name=permissions][data-role-id]')
                const inputPermissions = $('input[name=permissions]:not([data-role-id])')

                const buttonDropdownPilihan = $('button#pilihan')
                const buttonImpersonate = $('button#siap-impersonate')

                const buttonResetFilter = $('button#reset-filter')

                $(document).on('DOMContentLoaded', e => {
                    $('button#reset-filter').click(e => {
                        clearData()
                    })

                    buttonImpersonate.click(e => {
                        @this.impersonateAsUser(inputNRP.val())
                    })
                })

                $(document).on('data-saved', e => {
                    clearData()
                })

                $(document).on('data-denied', e => {
                    clearData()
                })

                function loadData(e) {
                    let { nrp, nama, roleIds, rolePermissionIds, permissionIds } = e.dataset
                    
                    setButtonState('disabled', false)

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

                    permissions = permissions.concat(rolePermissions)
                    permissions = removeDuplicates(permissions)

                    inputRoles.each((i, el) => el.checked = roles.find(v => v === el.value))
                    inputRolePermissions.each((i, el) => el.checked = permissions.find(v => v === el.value))
                    inputPermissions.each((i, el) => el.checked = permissions.find(v => v === el.value))                    

                    @this.emit('siap.prepare-la', nrp, nama)
                    @this.emit('siap.prepare-user', nrp, nama, roles, permissions)
                    @this.emit('siap.prepare-transfer', nrp, nama, roles, permissions)
                    @this.emit('khanza.prepare-user', nrp, nama)
                    @this.emit('khanza.prepare-transfer', nrp, nama)
                }

                function clearData() {
                    setButtonState('disabled', true)

                    inputNRP.val('')
                    inputNama.val('')
                    inputRoles.each((i, el) => el.checked = false)
                    inputRolePermissions.each((i, el) => el.checked = false)
                    inputPermissions.each((i, el) => el.checked = false)
                }

                function removeDuplicates(arr) {
                    return Array.from(new Set(arr))
                }

                function setButtonState(prop, state) {
                    buttonDropdownPilihan.prop(prop, state)
                }
            </script>
        @endpush
    @endonce

    <livewire:user.siap.set-perizinan />
    <livewire:user.siap.transfer-perizinan />
    <livewire:user.khanza.set-hak-akses />
    <livewire:user.khanza.transfer-hak-akses />
    <livewire:user.siap.lihat-aktivitas />

    <x-card>
        <x-slot name="header">
            <x-card.row>
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
                        <x-dropdown class="mb-3" livewire>
                            <x-slot name="button" class="btn-default" title="Pilihan" icon="fas fa-cogs" disabled></x-slot>
                            <x-slot name="menu" class="dropdown-menu-right">
                                <x-dropdown.header class="text-left">SIMRS Khanza</x-dropdown.header>
                                <x-dropdown.item-button id="khanza-set" icon="fas fa-user-cog fa-fw" title="Set Hak Akses" data-toggle="modal" data-target="#modal-khanza-set" />
                                <x-dropdown.item-button id="khanza-transfer" icon="fas fa-exchange-alt fa-fw" title="Transfer Hak Akses" data-toggle="modal" data-target="#modal-khanza-transfer" />
                                <x-dropdown.divider />
                                <x-dropdown.header class="text-left">SMC Internal App</x-dropdown.header>
                                <x-dropdown.item-button id="siap-set" icon="fas fa-user-cog fa-fw" title="Set Perizinan" data-toggle="modal" data-target="#modal-siap-set" />
                                <x-dropdown.item-button id="siap-transfer" icon="fas fa-exchange-alt fa-fw" title="Transfer Perizinan" data-toggle="modal" data-target="#modal-siap-transfer" />
                                <x-dropdown.divider />
                                <x-dropdown.item-button id="siap-impersonate" icon="fas fa-user-secret fa-fw" title="Impersonasi" />
                                <x-dropdown.item-button id="siap-aktivitas" icon="fas fa-binoculars fa-fw" title="Lihat Aktivitias" data-toggle="modal" data-target="#modal-aktivitas" />
                            </x-slot>
                        </x-dropdown>
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
                            <x-table.td clickable
                                data-nrp="{{ $user->nik }}"
                                data-nama="{{ $user->nama }}"
                                data-role-ids="{{ $user->roles->pluck('id')->join(',') }}"
                                data-role-permission-ids="{{ $user->getPermissionsViaRoles()->pluck('id')->join(',') }}"
                                data-permission-ids="{{ $user->permissions->pluck('id')->join(',') }}">
                                {{ $user->nik }}
                            </x-table.td>
                            <x-table.td>{{ $user->nama }}</x-table.td>
                            <x-table.td>{{ $user->jbtn }}</x-table.td>
                            <x-table.td>{{ $user->jenis }}</x-table.td>
                            <x-table.td>
                                <div style="display: inline-flex; flex-wrap: wrap; gap: 0.25rem">
                                    @foreach ($user->roles as $role)
                                        <x-badge class="badge-dark">{{ $role->name }}</x-badge>
                                    @endforeach
                                    @foreach ($user->permissions as $permission)
                                        <x-badge class="badge-secondary">{{ $permission->name }}</x-badge>
                                    @endforeach
                                </div>
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
