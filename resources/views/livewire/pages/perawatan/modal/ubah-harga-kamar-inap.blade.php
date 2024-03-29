<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                <x-slot name="columns">
                    {{-- <x-table.th name="id" title="#" /> --}}
                </x-slot>
                <x-slot name="body">
                    {{-- @forelse ($this->collectionProperty as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->id }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="1" />
                    @endforelse --}}
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
