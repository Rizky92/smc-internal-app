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

                    Livewire.on('flash', () => {
                        $('#hak-akses').modal('hide')
                    })
                })

                function loadData({
                    nrp,
                    nama,
                    roleIds,
                    permissionIds,
                }) {
                    inputNRP.val(nrp)
                    inputNama.val(nama)

                    let roles = Array.from(roleIds.split(','))
                    let permissions = Array.from(permissionIds.split(','))

                    inputRoles.each((i, el) => el.checked = roles.find(v => v === el.value))
                    inputPermissions.each((i, el) => el.checked = permissions.find(v => v === el.value))
                }

                $('#simpandata').click(e => {
                    let selectedRoles = []
                    let selectedPermissions = []

                    inputRoles.each((i, el) => {
                        if (el.checked) {
                            selectedRoles.push(el.value)
                        }

                        if (el.indeterminate) {
                            let inputRolePermissions = Array.from(el.nextElementSibling.nextElementSibling.children)

                            inputRolePermissions.forEach(el => {
                                let permissionCheckbox = el.children[0]

                                if (permissionCheckbox.checked) {
                                    selectedPermissions.push(permissionCheckbox.value)
                                }
                            })
                        }
                    })

                    @this.simpan(inputNRP.val(), selectedRoles, selectedPermissions)
                })

                $('input[type=checkbox]').change(function(e) {
                    var checked = $(this).prop("checked"),
                        container = $(this).parent(),
                        siblings = container.siblings()

                    container.find('input[type=checkbox]').prop({
                        indeterminate: false,
                        checked: checked
                    })

                    function checkSiblings(el) {
                        var parent = el.parent().parent(),
                            all = true

                        el.siblings().each(function() {
                            let returnValue = all = ($(this).children('input[type=checkbox]').prop("checked") === checked)

                            return returnValue
                        })

                        if (all && checked) {
                            parent.children('input[type=checkbox]').prop({
                                indeterminate: false,
                                checked: checked
                            })

                            checkSiblings(parent)
                        } else if (all && !checked) {
                            parent.children('input[type=checkbox]').prop("checked", checked)
                            parent.children('input[type=checkbox]').prop("indeterminate", (parent.find('input[type=checkbox]:checked').length > 0))

                            checkSiblings(parent)
                        } else {
                            el.parents("li").children('input[type=checkbox]').prop({
                                indeterminate: true,
                                checked: false
                            })
                        }
                    }

                    checkSiblings(container)
                })
            </script>
        @endpush
    @endonce

    <div class="modal fade" id="hak-akses">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Setup hak akses untuk user</h4>
                    <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                        <span aria-hidden="true">&times</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <ul class="form-group" id="role_permissions">
                                @foreach ($this->roles as $role)
                                    <li class="custom-control custom-checkbox">
                                        <input class="custom-control-input" id="role-{{ $role->id }}" name="roles" type=checkbox value="{{ $role->id }}">
                                        <label class="custom-control-label" for="role-{{ $role->id }}">{{ Str::of($role->name)->upper() }}</label>
                                        <ul class="form-group">
                                            @foreach ($role->permissions as $permission)
                                                <li class="custom-control custom-checkbox">
                                                    <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $permission->id }}" name="permissions" data-role-id="{{ $role->id }}" type=checkbox value="{{ $permission->id }}">
                                                    <label class="custom-control-label font-weight-normal" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                                <li style="list-style: none">
                                    <b>Hak akses lainnya</b>
                                    @foreach ($this->otherPermissions as $op)
                                    <li class="custom-control custom-checkbox">
                                        <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $op->id }}" name="permissions" type=checkbox value="{{ $op->id }}">
                                        <label class="custom-control-label font-weight-normal" for="permission-{{ $op->id }}">{{ $op->name }}</label>
                                    </li>
                                    @endforeach
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button class="btn btn-default" id="batalsimpan" data-dismiss="modal" type="button">Batal</button>
                    <button class="btn btn-primary" id="simpandata" type="button">
                        <i class="fas fa-save"></i>
                        <span class="ml-1">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                        <button class="btn btn-sm btn-default mb-3" data-toggle="modal" data-target="#hak-akses" type="button">
                            <i class="fas fa-info-circle"></i>
                            <span class="ml-1">Set hak akses</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <span class="text-sm pr-2">Tampilkan:</span>
                        <div class="input-group input-group-sm" style="width: 4rem">
                            <select class="custom-control custom-select" name="perpage" wire:model.defer="perpage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <span class="text-sm pl-2">per halaman</span>
                        <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                            <input type="search" class="form-control" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="searchData" />
                            <div class="input-group-append">
                                <button type="button" wire:click="searchData" class="btn btn-sm btn-default">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <a href="#"
                                    style="display: inline; position: absolute; left: 0; right: 0; top: 0; bottom: 0"
                                    data-nrp="{{ $user->nip }}"
                                    data-nama="{{ $user->nama }}"
                                    data-role-ids="{{ $user->roles->pluck('id')->join(',') }}"
                                    data-permission-ids="{{ $user->getAllPermissions()->pluck('id')->join(',') }}"
                                    onclick="loadData(this.dataset)"></a>
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
