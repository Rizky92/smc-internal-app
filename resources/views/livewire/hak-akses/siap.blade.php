<div>
    <x-flash />

    <livewire:hak-akses.siap.modal-perizinan />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                <x-button variant="primary" size="sm" title="Role Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-perizinan" class="btn-primary ml-3" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body">
            <x-table zebra hover sticky nowrap livewire>
                <x-slot name="columns">
                    <x-table.th title="#" />
                    <x-table.th style="width: 30ch" title="Nama" />
                    <x-table.th title="Perizinan yang Diberikan" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->roles as $role)
                        @php($superadmin = $role->name === config('permission.superadmin_name'))
                        <x-table.tr :class="Arr::toCssClasses(['text-muted' => $superadmin])">
                            <x-table.td>
                                @unless($superadmin)
                                    <x-button size="xs" variant="link" class="m-0 p-0 border-0" title="Edit" icon="fas fa-pencil-alt" data-toggle="modal" data-target="#modal-perizinan" wire:click="$emit('siap.prepare', {{ $role->id }})" />
                                @endunless
                            </x-table.td>
                            <x-table.td class="{{ Arr::toCssClasses(['pt-2' => !$superadmin]) }}">{{ $role->name }}</x-table.td>
                            <x-table.td>
                                <div style="display: inline-flex; flex-wrap: wrap; gap: 0.25rem">
                                    @if ($superadmin)
                                        *
                                    @endif
                                    @foreach ($role->permissions as $permission)
                                        <x-badge variant="secondary">{{ $permission->name }}</x-badge>
                                    @endforeach
                                </div>
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
