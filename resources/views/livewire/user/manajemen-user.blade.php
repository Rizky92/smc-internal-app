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

                function loadData({ nrp, nama, roleIds, permissionIds }) {
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

    <div class="card">
        <div class="card-body">
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
            <div class="row mt-3">
                <x-filter />
            </div>
        </div>
        <div class="card-body table-responsive p-0 border-top border-bottom">
            <table class="table table-hover table-striped table-sm text-sm" id="table_index">
                <thead>
                    <tr>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jenis</th>
                        <th>Hak Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->users as $user)
                        <tr style="position: relative">
                            <td>
                                {{ $user->nip }}
                                <a data-nrp="{{ $user->nip }}" data-nama="{{ $user->nama }}" data-role-ids="{{ $user->roles->pluck('id')->join(',') }}" data-permission-ids="{{ $user->getAllPermissions()->pluck('id')->join(',') }}" href="#" style="display: inline; position: absolute; left: 0; right: 0; top: 0; bottom: 0" onclick="loadData(this.dataset)"></a>
                            </td>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->nm_jbtn }}</td>
                            <td>Petugas</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    @php($first = $loop->first ? '' : 'ml-1')
                                    <span class="{{ $first }} badge badge-dark">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->users->count() }} dari total {{ number_format($this->users->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
