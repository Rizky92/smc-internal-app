<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let inputNRP
                let inputNama
                let inputRoles
                let inputPermissions

                $(document).ready(() => {
                    inputNRP = $('#user')
                    inputNama = $('#nama')
                    inputRoles = $('input[name=roles]')
                    inputPermissions = $('input[name=permissions]')
                })

                function loadData({
                    nrp,
                    nama,
                    roleIds,
                    permissionIds
                }) {
                    inputNRP.val(nrp)
                    inputNama.val(nama)

                    let roles = Array.from(roleIds.split(','))
                    let permissions = Array.from(permissionIds.split(','))

                    inputRoles.each((i, el) => el.checked = roles.find(v => v === el.value))
                    inputPermissions.each((i, el) => el.checked = permissions.find(v => v === el.value))

                    @this.emit('prepareTransfer', nrp, nama, roles, permissions)
                    @this.emit('prepareUser', nrp)
                }
            </script>
        @endpush
    @endonce

    <livewire:user.utils.set-hak-akses />

    <livewire:user.utils.transfer-hak-akses />

    <x-card>
        <x-slot name="header">
            <x-card.row wire:ignore>

            </x-card.row>
            <div class="row" wire:ignore>
                <div class="col-2">
                    <div class="form-group">
                        <label class="text-sm" for="user">NRP</label>
                        <input class="form-control form-control-sm bg-light" id="user" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="nama">Nama</label>
                        <input class="form-control form-control-sm bg-light" id="nama" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-end h-100">
                        <button class="btn btn-sm btn-default mb-3" data-toggle="modal" data-target="#hak-akses" type="button" id="button-set-hak-akses">
                            <i class="fas fa-info-circle"></i>
                            <span class="ml-1">Set hak akses</span>
                        </button>
    
                        <button class="btn btn-sm btn-default mb-3 ml-2" data-toggle="modal" data-target="#transfer-hak-akses" type="button" id="button-transfer-hak-akses">
                            <i class="fas fa-share-square"></i>
                            <span class="ml-1">Transfer hak akses</span>
                        </button>
                    </div>
                </div>
            </div>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive p-0">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>NRP</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Jabatan</x-table.th>
                    <x-table.th>Jenis</x-table.th>
                    <x-table.th>Hak Akses</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->users as $user)
                        <x-table.tr>
                            <x-table.td>
                                {{ $user->nip }}
                                <x-slot name="clickable" data-nrp="{{ $user->nip }}" data-nama="{{ $user->nama }}" data-role-ids="{{ $user->roles->pluck('id')->join(',') }}" data-permission-ids="{{ $user->getAllPermissions()->pluck('id')->join(',') }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $user->nama }}</x-table.td>
                            <x-table.td>{{ $user->nm_jbtn }}</x-table.td>
                            <x-table.td>Petugas</x-table.td>
                            <x-table.td>
                                @foreach ($user->roles as $role)
                                    <x-badge :class="Arr::toCssClasses(['badge-dark', 'ml-1' => $loop->first], ' ')">{{ $role->name }}</x-badge>
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
