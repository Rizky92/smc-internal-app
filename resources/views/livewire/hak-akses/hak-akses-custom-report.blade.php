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

                    console.log({
                        permissionIds,
                        roleId
                    });

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
                <div class="modal-body position-relative py-0">
                    <div class="row sticky-top bg-white py-3 px-3 mx-0">
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

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Perizinan yang Diberikan</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->roles as $role)
                        <x-table.tr :class="Arr::toCssClasses(['bg-dark text-light' => $role->name == config('permission.superadmin_name')])">
                            <x-table.td>
                                {{ $role->name }}
                                @unless ($role->name == config('permission.superadmin_name'))
                                    <x-slot name="clickable" data-role-id="{{ $role->id }}" data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}" data-toggle="modal" data-target="#permission-modal"></x-slot>
                                @endunless
                            </x-table.td>
                            <x-table.td>
                                @unless ($role->name === config('permission.superadmin_name'))    
                                    @foreach ($role->permissions as $permission)
                                        @php($br = !$loop->last ? '<br>' : '')
                                        {{ $permission->name }} {!! $br !!}
                                    @endforeach
                                @else
                                    *
                                @endunless
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->roles" />
        </x-slot>
    </x-card>
</div>
