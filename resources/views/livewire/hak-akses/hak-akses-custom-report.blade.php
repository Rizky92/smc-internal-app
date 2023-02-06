<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let buttonSimpan
                let buttonBatalSimpan
                let buttonResetFilter
                let buttonSetPermission
                let buttonDeleteRole

                let inputNamaRole
                let hiddenIdRole

                $(document).ready(() => {
                    buttonSimpan = $('button#simpan')
                    buttonBatalSimpan = $('button#batal-simpan')
                    buttonResetFilter = $('button#reset-filter')
                    buttonSetPermission = $('button#set-permission')
                    buttonDeleteRole = $('button#hapus-role')

                    inputNamaRole = $('input#nama_role')
                    hiddenIdRole = $('input#id_role')

                    buttonResetFilter.click(clearData)
                    buttonBatalSimpan.click(clearData)
                })

                $('input#nama_role').on('input', e => {
                    if (e.target.value !== "") {
                        buttonSimpan.prop('disabled', false)
                        buttonBatalSimpan.prop('disabled', false)
                    } else {
                        buttonSimpan.prop('disabled', true)
                        buttonBatalSimpan.prop('disabled', true)
                    }
                })

                function loadData({
                    roleId,
                    roleName,
                    permissionIds
                }) {
                    permissionIds = Array.from(permissionIds.split(','))

                    buttonSimpan.prop('disabled', false)
                    buttonBatalSimpan.prop('disabled', false)

                    buttonSetPermission.toggleClass('d-none', false)
                    buttonDeleteRole.toggleClass('d-none', false)

                    inputNamaRole.val(roleName)
                    inputNamaRole.trigger('change')

                    hiddenIdRole.val(roleId)
                    hiddenIdRole.trigger('change')

                    @this.emit('permissions.prepare', roleId, roleName, permissionIds)
                }

                function clearData() {
                    inputNamaRole.val('')
                    inputNamaRole.trigger('change')

                    hiddenIdRole.val(null)
                    hiddenIdRole.trigger('change')

                    buttonSimpan.prop('disabled', true)
                    buttonBatalSimpan.prop('disabled', true)

                    buttonSetPermission.toggleClass('d-none', true)
                    buttonDeleteRole.toggleClass('d-none', true)
                }
            </script>
        @endpush
    @endonce

    <livewire:hak-akses.custom-report.permission-modal />

    <x-card>
        <x-slot name="header">
            <x-card.row :livewire="true">
                <div class="col-6">
                    <div class="form-group">
                        <label class="text-sm" for="nama_role">Nama Role</label>
                        <input class="form-control form-control-sm" id="nama_role" type="text" autocomplete="off" wire:model="roleName">
                        <input type="hidden" id="id_role" wire:model="roleId">
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-start align-items-end h-100">
                        <x-button disabled class="btn-sm btn-default mb-3" title="Batal" id="batal-simpan" />
                        <x-button disabled class="btn-sm btn-primary mb-3 ml-2" title="Simpan" icon="fas fa-save" wire:click="createOrUpdateRole" />
                        <x-button class="btn-sm btn-default mb-3 ml-2 d-none" title="Set Permission" icon="fas fa-edit" data-toggle="modal" data-target="#modal-role-permissions" />
                        <x-button class="btn-sm btn-danger mb-3 ml-auto d-none" title="Hapus Role" icon="fas fa-trash-alt" />
                    </div>
                </div>
            </x-card.row>
            <x-card.row-col class="mt-3">
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
                        @php($develop = $role->name === config('permission.superadmin_name'))
                        <x-table.tr :class="Arr::toCssClasses(['text-muted' => $develop])">
                            <x-table.td :clickable="!$develop" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}">
                                {{ $role->name }}
                            </x-table.td>
                            <x-table.td>
                                @if ($develop)
                                    *
                                @endif
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
