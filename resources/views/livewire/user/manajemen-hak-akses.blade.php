<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let inputRole
                let inputPermissions

                $(document).ready(() => {
                    inputRole = $('input[type=hidden][name=role]')
                    inputPermissions = $('input[name=permissions]')
                })

                function loadData({
                    roleId,
                    permissionIds,
                }) {
                    inputRole.val(roleId)

                    let permissions = Array.from(permissionIds.split(','))

                    console.log({permissionIds, roleId});

                    inputPermissions.each((i, el) => el.checked = permissions.find(v => v === el.value))
                }

                $('#simpandata').click(() => {
                    let currentRoleId = inputRole.val()
                    let currentPermissionsIds = []

                    inputPermissions.each((i, el) => {
                        currentPermissionsIds.push(el.checked && el.value)
                    })

                    @this.updatePermissions(currentRoleId, currentPermissionsIds)
                })
            </script>
        @endpush
    @endonce

    <div class="modal fade" id="permission-modal">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Set perizinan per hak akses</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body position-relative">
                    <div class="row sticky-top bg-white pb-3 px-0 mx-0">
                        <div class="col-12">
                            <div class="d-flex justify-content-start align-items-center">
                                <label for="cari_permission" class="pr-2 mt-1 text-sm">Cari permission: </label>
                                <input type="text" wire:model.defer="searchPermissions" id="cari_permission" class="form-control form-control-sm w-50">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <ul class="form-group" id="role_permissions">
                                <input type="hidden" name="role" class="d-none">
                                @foreach ($this->permissions as $key => $name)
                                    <li class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="permission-{{ $key }}" value="{{ $key }}" name="permissions">
                                        <label for="permission-{{ $key }}" class="custom-control-label font-weight-normal">{{ $name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="batalsimpan">Batal</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="simpandata">
                        <i class="fas fa-save"></i>
                        <span class="ml-1">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <span class="text-sm pr-2">Tampilkan:</span>
                        <div class="input-group input-group-sm" style="width: 4rem">
                            <select name="perpage" class="custom-control custom-select" wire:model.defer="perpage">
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
            <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Perizinan yang diberikan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->roles as $role)
                        <tr style="position: relative">
                            <td>
                                {{ Str::upper($role->name) }}
                                <a href="#" style="display: inline; position: absolute; left: 0; right: 0; top: 0; bottom: 0;"
                                    data-role-id="{{ $role->id }}"
                                    data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}"
                                    data-toggle="modal"
                                    data-target="#permission-modal"
                                    onclick="loadData(this.dataset)"
                                ></a>
                            </td>
                            <td>
                                <p>
                                    @if ($role->name === config('permission.superadmin_name')) * @endif
                                    @foreach ($role->permissions as $permission)
                                        @php($br = $loop->last ? '' : '<br>')
                                        {{ $permission->name }} {!! $br !!}
                                    @endforeach
                                </p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->roles->count() }} dari total {{ number_format($this->roles->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>