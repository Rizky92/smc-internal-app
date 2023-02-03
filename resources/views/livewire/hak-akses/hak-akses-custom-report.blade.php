<div>
    <x-flash />

    <livewire:hak-akses.custom-report.permission-modal />

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
