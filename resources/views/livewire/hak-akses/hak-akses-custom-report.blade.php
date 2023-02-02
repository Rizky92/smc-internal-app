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

    <x-modal :livewire="true" id="modal-role-permissions" title="Set Permission untuk Role">
        <x-slot name="body" class="position-relative py-0">
            <x-row-col>
                <ul class="form-group" id="role_permissions">
                    <input type="hidden" name="role" class="d-none">
                    @foreach ($this->permissions as $role => $permissions)
                        
                    @endforeach
                    @foreach ($this->permissions as $key => $name)
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="permission-{{ $key }}" value="{{ $key }}" name="permissions">
                            <label for="permission-{{ $key }}" class="custom-control-label font-weight-normal">{{ $name }}</label>
                        </li>
                    @endforeach
                </ul>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button class="btn-default" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button class="btn-primary" data-dismiss="modal" id="simpandata" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>

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
                    <x-table.th name="name" title="Nama" />
                    <x-table.th title="Perizinan yang Diberikan" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->roles as $role)
                        @php($su = $role->name === config('permission.superadmin_name'))
                        <x-table.tr :class="Arr::toCssClasses(['text-muted' => $su])">
                            <x-table.td>
                                {{ $role->name }}
                                @unless($su)
                                    <x-slot
                                        name="clickable"
                                        data-role-id="{{ $role->id }}"
                                        data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}"
                                        data-toggle="modal"
                                        data-target="#modal-role-permissions"
                                    ></x-slot>
                                @endunless
                            </x-table.td>
                            <x-table.td>
                                @if ($su) * @endif
                                @foreach ($role->permissions as $permission)
                                    @php($br = !$loop->last ? '<br>' : '')
                                    {{ $permission->name }} {!! $br !!}
                                @endforeach
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
