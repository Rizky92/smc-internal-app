<div>
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let {
                        roleId,
                        roleName,
                        permissionIds
                    } = e.dataset

                    permissionIds = Array.from(permissionIds.split(','))

                    @this.emit('siap.prepare', roleId, roleName, permissionIds)
                }
            </script>
        @endpush
    @endonce

    <livewire:hak-akses.modal.siap />

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
                    <x-table.th title="Nama" />
                    <x-table.th title="Perizinan yang Diberikan" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->roles as $role)
                        @php($superadmin = $role->name === config('permission.superadmin_name'))
                        <x-table.tr :class="Arr::toCssClasses(['text-muted' => $superadmin])">
                            <x-table.td :clickable="!$superadmin" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}" data-toggle="modal" data-target="#modal-role-permissions">
                                {{ $role->name }}
                            </x-table.td>
                            <x-table.td>
                                @if ($superadmin)
                                    *
                                @endif
                                @foreach ($role->permissions as $permission)
                                    {{ $permission->name }} @if (!$loop->last)
                                        <br>
                                    @endif
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
