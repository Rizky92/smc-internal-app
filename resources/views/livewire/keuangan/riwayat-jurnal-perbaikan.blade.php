<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap livewire>
                <x-slot name="columns">
                    {{-- <x-table.th name="id" title="#" /> --}}
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collectionProperty as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->id }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="1" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
