<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                const inputNRP = $('input#user-nrp')
                const inputNama = $('input#user-nama')

                const inputRoles = $('input[name=roles]')
                const inputRolePermissions = $('input[name=permissions][data-role-id]')
                const inputPermissions = $('input[name=permissions]:not([data-role-id])')

                const buttonDropdownPilihan = $('button#pilihan')
                const buttonImpersonate = $('button#button-impersonasi')

                const buttonResetFilter = $('button#reset-filter')

                $(document).on('DOMContentLoaded', e => {
                    $('button#reset-filter').click(e => clearData())

                    buttonImpersonate.click(e => {
                        @this.impersonateAsUser(inputNRP.val())
                    })
                })

                $(document).on('data-saved', e => clearData())
                $(document).on('data-denied', e => clearData())
                $(document).on('hidden.bs.modal', e => clearData())

                function loadData(e) {
                    let { nrp, nama, roleIds, rolePermissionIds, permissionIds } = e.dataset

                    buttonDropdownPilihan.prop('disabled', false)

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

                    @this.emit('user.prepare', nrp, nama, roles, permissions)
                }

                function clearData() {
                    buttonDropdownPilihan.prop('disabled', true)

                    inputNRP.val('')
                    inputNama.val('')
                }

                function removeDuplicates(arr) {
                    return Array.from(new Set(arr))
                }
            </script>
        @endpush
    @endonce

    <livewire:pages.user.siap.set-perizinan />
    <livewire:pages.user.siap.transfer-perizinan />
    <livewire:pages.user.khanza.set-hak-akses />
    <livewire:pages.user.khanza.transfer-hak-akses />
    <livewire:pages.user.siap.lihat-aktivitas />

    <x-card use-loading>
        <x-slot name="header">
            <x-row>
                <div class="col-2">
                    <div class="form-group">
                        <label class="text-sm" for="user-nrp">NRP</label>
                        <input class="form-control form-control-sm" id="user-nrp" type="text" readonly autocomplete="off" />
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="user-nama">Nama</label>
                        <input class="form-control form-control-sm" id="user-nama" type="text" readonly autocomplete="off" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-end h-100">
                        <div class="mb-3 d-flex align-items-center w-100">
                            <x-dropdown livewire menu-position="right">
                                <x-slot name="button" size="sm" title="Pilihan" icon="fas fa-cogs" disabled></x-slot>
                                <x-slot name="menu">
                                    <x-dropdown.header class="text-left">SIMRS Khanza</x-dropdown.header>
                                    <x-dropdown.item as="button" id="button-set-hak-akses" icon="fas fa-user-cog fa-fw" title="Set Hak Akses" data-toggle="modal" data-target="#modal-set-hak-akses" />
                                    <x-dropdown.item
                                        as="button"
                                        id="button-transfer-hak-akses"
                                        icon="fas fa-exchange-alt fa-fw"
                                        title="Transfer Hak Akses"
                                        data-toggle="modal"
                                        data-target="#modal-transfer-hak-akses" />
                                    <x-dropdown.divider />
                                    <x-dropdown.header class="text-left">SMC Internal App</x-dropdown.header>
                                    <x-dropdown.item as="button" id="button-set-perizinan" icon="fas fa-user-cog fa-fw" title="Set Perizinan" data-toggle="modal" data-target="#modal-set-perizinan" />
                                    <x-dropdown.item
                                        as="button"
                                        id="button-transfer-perizinan"
                                        icon="fas fa-exchange-alt fa-fw"
                                        title="Transfer Perizinan"
                                        data-toggle="modal"
                                        data-target="#modal-transfer-perizinan" />
                                    <x-dropdown.divider />
                                    <x-dropdown.item as="button" id="button-impersonasi" icon="fas fa-user-secret fa-fw" title="Impersonasi" />
                                    <x-dropdown.item
                                        as="button"
                                        id="button-lihat-aktivitas"
                                        icon="fas fa-binoculars fa-fw"
                                        title="Lihat Aktivitias"
                                        data-toggle="modal"
                                        data-target="#modal-lihat-aktivitas" />
                                </x-slot>
                            </x-dropdown>
                            <x-filter.toggle class="ml-auto" model="tampilkanYangMemilikiHakAkses" title="Tampilkan yang Memiliki Hak Akses" />
                        </div>
                    </div>
                </div>
            </x-row>
            <x-row-col-flex>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nik" title="NRP" style="width: 15ch" />
                    <x-table.th name="nama" title="Nama" style="width: 50ch" />
                    <x-table.th name="jbtn" title="Jabatan" style="width: 30ch" />
                    <x-table.th name="jenis" title="Jenis" style="width: 10ch" />
                    <x-table.th>Hak Akses</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->users as $user)
                        <x-table.tr>
                            <x-table.td
                                clickable
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
                                <div class="d-inline-flex flex-wrap" style="gap: 0.25rem">
                                    @foreach ($user->roles as $role)
                                        <x-badge variant="dark">
                                            {{ $role->name }}
                                        </x-badge>
                                    @endforeach

                                    @foreach ($user->permissions as $permission)
                                        <x-badge variant="secondary">
                                            {{ $permission->name }}
                                        </x-badge>
                                    @endforeach
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->users" />
        </x-slot>
    </x-card>
</div>
